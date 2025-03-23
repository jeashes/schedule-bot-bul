<?php

namespace App\Service\OpenAi;

use Illuminate\Support\Facades\Log;

class KnowledgeLevelValidator
{
    public function __construct(private readonly DataCreator $dataCreator) {}

    public function validateKnowledgeLevel(string $title, string $knowledgeLevel): int
    {
        $systemPrompt = 'You are an expert evaluator for educational levels in various technical subjects. Your task is to check whether the provided level of expertise correctly matches the subject area that the learner intends to study.

                        For instance, consider the following examples:
                        - Subject: "GOF design patterns in PHP"
                        Correct Level: "strong junior backend dev"
                        - Subject: "GOF design patterns in PHP"
                        Incorrect Level: "avkhawsvhsomething nonsense"

                        When given a subject and a proposed knowledge level:
                        1. Validate if the level description is meaningful, follows professional standards, and is appropriate for the subject area.
                        2. If the level is valid and properly correlates with the subject, reply with a confirmation that the level is acceptable.
                        3. If the level is not appropriate or appears to be invalid, provide a clear message stating that the level description does not meet the criteria, and suggest checking the input.

                        Example Input:
                        Subject: "GOF design patterns in PHP"
                        Proposed Level: "strong junior backend dev"

                        Expected Output:
                        JSON FORMAT: {"found": 1} - when knowledge level validated and correct. In another case YOU SHOULD RETURN IN FORMAT {"found": 0}';

        $body = "Title: $title, knowledge level on validation: $knowledgeLevel";

        Log::channel('telegram')->debug('KNOWLEDGE_LEVEL: '.$this->dataCreator->aiAnalyze($systemPrompt, $body));
        $data = json_decode($this->dataCreator->aiAnalyze($systemPrompt, $body), true);

        return $data['found'] ?? 0;
    }
}
