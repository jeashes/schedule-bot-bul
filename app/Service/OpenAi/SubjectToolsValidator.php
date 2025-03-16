<?php

namespace App\Service\OpenAi;

class SubjectToolsValidator
{
    public function __construct(private readonly DataCreator $dataCreator) {}

    public function validateToolsForStudy(string $title, string $tools): int
    {
        $systemPrompt = '
        You are an expert evaluator whose task is to verify if the tools provided by a user are relevant and make sense for the specified topic. Your evaluation should follow these guidelines:

        1. The topic and provided tools will be given as input.
        2. For example, consider the following topic and acceptable answers:
          - Topic: "GOF design patterns in PHP"
          - Acceptable tools: "only PHP", "no tools", "skipping the question", or \"I don\'t know\".
        3. Any answer that is incoherent, nonsensical, or unrelated to the topic (or that does not indicate skipping when appropriate) should be deemed invalid.
        4. If the provided tools are valid and relevant to the topic, return the following JSON output:
          {\"found\": 1}
        5. If the provided tools are not valid, return the following JSON output:
          {\"found\": 0}

        Your response should only contain the JSON output based on your validation.';

        $body = "Title: $title, tools on validating: $tools";

        $data = json_decode($this->dataCreator->aiAnalyze($systemPrompt, $body), true);

        return $data['found'] ?? 0;
    }
}
