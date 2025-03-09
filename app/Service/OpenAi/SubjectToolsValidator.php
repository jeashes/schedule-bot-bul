<?php

namespace App\Service\OpenAi;

class SubjectToolsValidator
{
    public function __construct(private readonly DataCreator $dataCreator) {}

    public function validateToolsForStudy(string $title, string $tools): int
    {
        $systemPrompt = "Act as an AI evaluator for study tools $tools. Your task is to assess and validate a list of tools recommended for a specific study subject title - $title. Follow these steps:

        1. Validate Tool Relevance: Confirm that each tool is appropriate and effective for the given subject title - $title, ensuring alignment with its core concepts.
        2. Check Clarity and Description: Evaluate the clarity and accuracy of the provided descriptions for each tool. Identify any ambiguous or incorrect details.
        3. Assess Educational Value: Determine if the $tools enhance understanding of the subject - $title, offer practical applicability, and meet academic standards.
        4. Provide Improvement Suggestions: Recommend modifications or additional tools to better support the study subject title - $title.";

        $body = "Try validate tools of $tools for subject title $title on info that you can find in your data."
        .'If you cannot find anything or something strange that has not relate to subject title.'
        .'YOU SHOULD RETURN ANSWER IN JSON FORMAT: {"found": 0}. In another case YOU SHOULD RETURN IN FORMAT {"found": 1}
          Additional info: tools titles format "i dont know" or something near to this also should return response {"format": 1}
        ';

        $data = json_decode($this->dataCreator->aiAnalyze($systemPrompt, $body), true);

        return $data['found'] ?? 0;
    }
}
