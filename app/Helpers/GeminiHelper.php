<?php

namespace App\Helpers;

use App\Models\Estimative;
use App\Models\Scope;
use GeminiAPI\Client;
use GeminiAPI\Resources\ModelName;
use GeminiAPI\Resources\Parts\TextPart;
use Smalot\PdfParser\Parser as PdfParser;

class GeminiHelper
{
    protected Client $client;
    protected array $messages = [];
    protected float $temperature = 0.7;

    public function __construct()
    {
        $this->client = new Client(env('GEMINI_API_KEY'));
    }


    public function addMessage(string $role, string $content): self
    {
        $this->messages[] = ['role' => $role, 'parts' => [$content]];
        return $this;
    }

    public function addFileContent(string $path): self
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $content = '';

        if ($extension === 'pdf') {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($path);
            $content = $pdf->getText();
        } else {
            $content = file_get_contents($path);
        }

        return $this->addMessage('user', "Conteúdo do arquivo:\n" . $content);
    }

    public function setTemperature(float $value): self
    {
        $this->temperature = $value;
        return $this;
    }

    public function addApprovedEstimativesContext(): self
    {
        $estimatives = Estimative::where('approval', 'approved')->where('status', 1)->get();

        if ($estimatives->isEmpty()) {
            return $this->addMessage('user', "⚠️ Não há estimativas aprovadas salvas no sistema.");
        }

        $contextText = $estimatives->map(function ($e) {
            return <<<TXT
                Escopo:
                {$e->scope->content}

                Tempo médio: {$e->estimated_hours_average}h
                Valor total médio: R$ {$e->total_value_average}
                Valor hora usado: R$ {$e->hourly_rate}

                ---
            TXT;
        })->implode("\n");

        return $this->addMessage('user', "Base de dados de estimativas aprovadas:\n\n" . $contextText);
    }


   
    public function generate(): string
    {
        
        $parts = array_map(
            fn($message) => new TextPart($message['parts'][0]),
            $this->messages
        );
        
        $response = $this->client->generativeModel(ModelName::GEMINI_1_5_FLASH)
            ->generateContent(...$parts);
        
        return $response->text();
    }

    public static function generateScopeFromTranscript(string $transcriptContent): string
    {
        $approvedScopes = Scope::where('approval', 'approved')
            ->where('status', 1)
            ->latest()
            ->pluck('content')
            ->toArray();

        $examples = implode("\n\n----------------------\n\n", $approvedScopes);

        $prompt = <<<EOT
            Você é um analista de requisitos com experiência em desenvolvimento de software web. Seu objetivo é transformar transcrições de reuniões em escopos técnicos claros, detalhados e com estrutura padronizada.

            ---

            ### 🔍 Instruções:

            1. **Leia cuidadosamente a transcrição da reunião abaixo.**
            2. Baseando-se nas informações fornecidas e nos escopos anteriormente aprovados, **gere um escopo técnico completo.**
            3. Siga exatamente o modelo de estrutura abaixo:
                - Título principal com número (ex: **01. Plataforma Web**)
                - Descrição do módulo
                - Sessão **"Escopo do Projeto"** com seções nomeadas (SITE, ADMIN, etc.)
                - Listas com bullets, subtópicos e observações em *itálico* onde aplicável
                - Ao final, incluir seção “🕑 Cronograma de Desenvolvimento”

            4. O escopo deve ser escrito em português formal e técnico, mas acessível para não desenvolvedores.

            ---

            ### 📎 Exemplos de escopos aprovados:
            {$examples}

            ---

            ### 📃 Transcrição da reunião:
            {$transcriptContent}

            ---

            🛑 **Importante**: Retorne **apenas o escopo** no formato Markdown. **Não adicione nenhuma explicação ou comentário fora do texto do escopo.**
        EOT;

        return (new self())
            ->addMessage('user', $prompt)
            ->generate();
    }

    public static function generateEstimativeFromScope(string $scopeContent, array $context = []): string
    {
        $hourlyRate = $context['Valor hora'] ?? 120;

        $contextText = collect($context)
            ->map(fn($v, $k) => "$k: $v")
            ->implode("\n");


            $prompt = <<<EOT
                    Você é um assistente técnico especializado em planejamento de projetos de software. Você atua como analista de estimativas, com acesso a dados históricos de projetos estimados, aprovados e executados.

                    Com base no escopo fornecido e no contexto adicional abaixo, sua tarefa é gerar uma **estimativa estruturada e detalhada** em formato **JSON**, que será consumido por uma API para análise automatizada.

                    ---

                    ⚠️ **Orientações importantes**:

                    - Se existirem estimativas anteriores aprovadas (fornecidas no contexto), use-as como **base comparativa principal**.
                    - Se **não houver estimativas anteriores**, **ainda assim** forneça uma estimativa coerente com base no escopo, e **informe isso claramente** nos campos `observations` e `general_notes`.

                    🔍 **Detalhamento exigido**:

                    - Os campos `observations`, `complexity_level`, `risks`, `influencing_factors`, `recommendations` e `general_notes` devem ser **completos e descritivos**, com justificativas técnicas reais, exemplos, possíveis causas e implicações.
                    - Evite frases genéricas. Dê explicações claras de **como o raciocínio foi feito**, o que influenciou a estimativa e quais aspectos merecem atenção.
                    - Use termos técnicos e linguagem precisa. Seja objetivo, mas informativo.

                    O campo risks deve ser um array de objetos, cada um contendo os campos: description, probability, impact, mitigation.

                    📦 **Formato esperado**:

                    
                    {
                        "hourly_rate": {$hourlyRate},
                        "estimates": {
                            "optimistic": {
                                "hours": "",
                                "total_value": "",
                                "observations": ""
                            },
                            "average": {
                                "hours": "",
                                "total_value": "",
                                "observations": ""
                            },
                            "pessimistic": {
                                "hours": "",
                                "total_value": "",
                                "observations": ""
                            }
                        },
                        "complexity_level": "",
                        "risks": [
                            {
                                "description": "",
                                "probability": "Baixa | Média | Alta",
                                "impact": "Baixo | Médio | Alto",
                                "mitigation": ""
                            }
                        ],
                        "influencing_factors": [],
                        "recommendations": [],
                        "general_notes": ""
                    }

                    ⚠️ Se não houver dados históricos suficientes, adicione a seguinte observação em general_notes ou observations:
                    “Não encontramos nenhuma estimativa previamente aprovada no sistema para usar como base comparativa. Isso pode reduzir a precisão desta estimativa. Para obter resultados mais realistas e adaptados ao seu negócio, recomendamos cadastrar pelo menos 5 escopos com suas respectivas estimativas já aprovadas (tempo e valor). Esses dados serão utilizados como histórico para calibrar futuras previsões com mais inteligência contextual.”

                    NUNCA retorne nenhum texto fora do JSON. Nenhum comentário, título, explicação ou formatação adicional fora da estrutura.

                    📚 Contexto adicional:
                    $contextText

                    📄 Escopo atual:
                    $scopeContent
                EOT;


        
        return (new self())
            ->addApprovedEstimativesContext()
            ->addMessage('user', $prompt)
            ->generate();
    }

}
