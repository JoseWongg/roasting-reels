<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;


class Translator
{
    private Client $client;
    private string $apiKey;// The API key for the OpenAI API
    private LoggerInterface $logger;


    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->apiKey = $_ENV['OPENAI_API_KEY'] ?? throw new \RuntimeException('OPENAI_API_KEY is not set in environment variables.');

        $cacertPath = $_ENV['CACERT_PATH'] ?? throw new \RuntimeException('CACERT_PATH is not set in environment variables.');
        if (!file_exists($cacertPath)) {
            $this->logger->error('CA bundle file not found at: ' . $cacertPath);
            throw new \RuntimeException('CA bundle file not found at: ' . $cacertPath);
        }

        $this->client = new Client(['verify' => $cacertPath]);
    }

    // Method to translate a given text into another language using the GPT-4 model.
    public function translateText(string $sourceText, string $translateTo): array
    {
        $url = 'https://api.openai.com/v1/chat/completions';

        // Custom prompt to be sent to the API
        $customPrompt = <<<PROMPT
Translate the following text into {$translateTo} if it is not already in that language. If the text is already in {$translateTo}, leave it as is. Do not include in your response any additional commentary or explanation.

Text to translate: "{$sourceText}"
PROMPT;

        try {
            $response = $this->client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4',
                    'messages' => [
                        ["role" => "system", "content" => "Your task is to identify the language of a given text and translate it into another given language if it's not already in the given language. Include the original language in the translation prefix."],
                        ["role" => "user", "content" => $customPrompt],
                    ],
                ],
            ]);

            $body = $response->getBody();
            $content = json_decode($body->getContents(), true);
            $this->logger->info('API response received:', ['response' => $content]);

            // Check if the 'choices' array exists and has at least one element
            if (!empty($content['choices'][0]['message']['content'])) {
                return ['response' => $content['choices'][0]['message']['content']];
            } else {
                $this->logger->error('Unexpected API response structure.', ['response' => $content]);
                return ['error' => 'Unexpected API response structure.'];
            }
        } catch (GuzzleException $e) {
            $this->logger->error('API request failed:', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }
}



