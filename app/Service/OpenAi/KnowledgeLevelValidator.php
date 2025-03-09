<?php

namespace App\Service\OpenAi;

class KnowledgeLevelValidator
{
    public function __construct(private readonly DataCreator $dataCreator) {}

    public function validateKnowledgeLevel(string $title, string $knowledgeLevel): int
    {
        $systemPrompt = "Act as an AI evaluator of $knowledgeLevel for subject - $title. Your task is to assess and validate a user's knowledge level in a specific subject $title based on their provided response. Follow these steps:

        1. Evaluate Depth of Understanding: Analyze the response for completeness, accuracy, and depth of content.
        2. Determine Knowledge Level: Classify the user's knowledge into one of the following categories: Beginner, Intermediate, or Advanced.
        3. Provide Feedback: Offer a concise summary of the evaluation, highlighting strengths and areas for improvement.
        4. Recommend Next Steps: Suggest further study or practice materials appropriate for the assessed level.";

        $body = "Try validate knowledge level - $knowledgeLevel of subject $title on info that you can find in your data."
        .'If you cannot find anything or something strange that has not relate to knowledge level of subject'
        .'YOU SHOULD RETURN ANSWER IN JSON FORMAT: {"found": 0}. In another case YOU SHOULD RETURN IN FORMAT {"found": 1}';

        $data = json_decode($this->dataCreator->aiAnalyze($systemPrompt, $body), true);

        return $data['found'] ?? 0;
    }
}
