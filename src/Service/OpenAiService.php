<?php

namespace App\Service;

use OpenAI;

class OpenAiService
{
    private $client;

    public function __construct(string $apiKey)
    {
        $this->client = OpenAI::client($apiKey);
    }

    public function generateResponse(string $prompt): string
    {
        $response = $this->client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        return $response->choices[0]->message->content;
    }

    public function validateUsername(string $username): bool
    {
        try {
            $prompt = "Vérifiez si ce nom d'utilisateur est approprié et ne contient pas de contenu offensant ou inapproprié. " .
                "Répondez uniquement par 'true' si c'est approprié ou 'false' si ce n'est pas approprié. " .
                "Nom d'utilisateur à vérifier : " . $username;

            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            $result = strtolower(trim($response->choices[0]->message->content));
            
            if ($result !== 'true' && $result !== 'false') {
                throw new \RuntimeException('Réponse inattendue de l\'API : ' . $result);
            }
            
            return $result === 'true';
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de la validation du nom d\'utilisateur : ' . $e->getMessage());
        }
    }

    public function validateReviewContent(string $content): bool
    {
        try {
            $prompt = "En tant que modérateur, analysez ce commentaire et déterminez s'il est approprié. " .
                "Le commentaire doit être rejeté s'il contient : " .
                "- Du contenu offensant ou haineux " .
                "- Des insultes ou du harcèlement " .
                "- Des menaces ou de la violence " .
                "- Du contenu discriminatoire " .
                "Répondez uniquement par 'true' si le commentaire est approprié ou 'false' s'il doit être rejeté. " .
                "Commentaire à analyser : " . $content;

            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            $result = strtolower(trim($response->choices[0]->message->content));
            
            if ($result !== 'true' && $result !== 'false') {
                throw new \RuntimeException('Réponse inattendue de l\'API : ' . $result);
            }
            
            return $result === 'true';
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de la validation du commentaire : ' . $e->getMessage());
        }
    }

    public function generateArticle(string $prompt): array
    {
        try {
            $systemPrompt = "En tant que rédacteur professionnel, créez un article de blog structuré dans la même langue que le sujet. " .
                "La réponse DOIT être un objet JSON valide avec exactement cette structure : " .
                "{ \"title\": \"Le titre de l'article\", \"content\": \"Le contenu de l'article\", \"language\": \"Le code langue de l'article\", \"categories\": [\"Catégorie1\", \"Catégorie2\", ...] }. " .
                "Le titre doit être accrocheur avec maximum 255 caractères et le contenu doit être informatif et engageant avec maximum 10000 caractères, " .
                "avec des paragraphes bien structurés. " .
                "Pour les catégories, en choisir une ou plusieurs qui se rapproche le plus parmis : Travel, Sport, Politics, Economy et Culture.";

            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
            ]);

            $content = $response->choices[0]->message->content;
            $decodedContent = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('La réponse de l\'API n\'est pas un JSON valide : ' . json_last_error_msg());
            }

            if (!isset($decodedContent['title']) || !isset($decodedContent['content']) || !isset($decodedContent['categories'])) {
                throw new \RuntimeException('Format de réponse invalide de l\'API. La réponse reçue : ' . $content);
            }

            return [
                'title' => $decodedContent['title'],
                'content' => $decodedContent['content'],
                'language' => $decodedContent['language'],
                'categories' => $decodedContent['categories']
            ];
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de la génération de l\'article : ' . $e->getMessage());
        }
    }

    public function translateArticle(string $title, string $content, string $targetLanguage): array
    {
        try {
            $systemPrompt = "En tant que traducteur professionnel, traduisez cet article en {$targetLanguage}. " .
                "Votre réponse DOIT être un objet JSON valide avec exactement cette structure : " .
                "{ \"title\": \"Le titre traduit\", \"content\": \"Le contenu traduit\" }. " .
                "Gardez le même style et le même ton que l'original.";

            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => "Titre original : {$title}\nContenu original : {$content}"],
                ],
                'temperature' => 0.7,
            ]);

            $content = $response->choices[0]->message->content;
            $decodedContent = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('La réponse de l\'API n\'est pas un JSON valide : ' . json_last_error_msg());
            }

            if (!isset($decodedContent['title']) || !isset($decodedContent['content'])) {
                throw new \RuntimeException('Format de réponse invalide de l\'API. La réponse reçue : ' . $content);
            }

            return [
                'title' => $decodedContent['title'],
                'content' => $decodedContent['content']
            ];
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de la traduction de l\'article : ' . $e->getMessage());
        }
    }
}
