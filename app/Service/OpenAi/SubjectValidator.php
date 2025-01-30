<?php

namespace App\Service\OpenAi;

class SubjectValidator
{
    public function __construct(private readonly DataCreator $dataCreator)
    {

    }

    public function validateSubjectTitle(string $title): bool
    {
        $systemPrompt = 'Act as an AI assistant for a teacher. Your task is to find relevant information about a given subject and validate the accuracy of the subject title. Follow these steps:

            Validate the Subject Title: Check if the given subject title is correct, widely recognized, and appropriately named in an academic or professional context. If needed, suggest a more accurate or commonly used title.
            Provide Key Information: Summarize essential details about the subject, including its definition, key concepts, applications, and significance.
            Suggest Additional Resources: Recommend credible sources (e.g., books, articles, or websites) where more information can be found.
            Ensure Accuracy & Relevance: Cross-check information from reliable sources and ensure it aligns with educational standards.

        The response should be concise, well-structured, and tailored for a teacher preparing lessons or verifying a subject title.';

        $body = "Try validate subject title $title on info that you can find in your data."
        . 'If you cannot find anything or something strange that has not relate to subject title'
        . 'YOU SHOULD RETURN ANSWER IN JSON FORMAT: {"found": 0}. In another case YOU SHOULD RETURN IN FORMAT {"found": 1}';

        $data = json_decode($this->dataCreator->aiAnalyze($systemPrompt, $body), true);

        return $data;
    }
}
