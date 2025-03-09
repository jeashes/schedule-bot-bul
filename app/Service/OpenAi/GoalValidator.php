<?php

namespace App\Service\OpenAi;

class GoalValidator
{
    public function __construct(private readonly DataCreator $dataCreator) {}

    public function validateLearnGoal(string $title, string $goal): int
    {
        $systemPrompt = "Act as an AI assistant for a teacher. Your task is to validate a user's goal based on the provided subject title. Follow these steps:

        1. Validate the Alignment: Check whether the user's goal logically aligns with the provided subject title - $title. Evaluate if the goal accurately reflects the key themes and concepts related to the title.
        2. Check Clarity and Consistency: Determine if the goal is clearly defined, well-structured, and logically consistent. Identify any ambiguous or contradictory elements.
        3. Evaluate Academic Relevance: Assess if the goal is academically sound and appropriate for the subject - $title, considering educational standards.
        4. Provide Improvement Suggestions: Recommend refinements or adjustments to better align the goal with the subject title - $title and enhance clarity and effectiveness.";

        $body = "Try validate goal $goal on info that you can find in your data."
        ."If you cannot find anything or something strange that has not relate to goal - $goal"
        .'YOU SHOULD RETURN ANSWER IN JSON FORMAT: {"found": 0}. In another case YOU SHOULD RETURN IN FORMAT {"found": 1}';

        $data = json_decode($this->dataCreator->aiAnalyze($systemPrompt, $body), true);

        return $data['found'] ?? 0;
    }
}
