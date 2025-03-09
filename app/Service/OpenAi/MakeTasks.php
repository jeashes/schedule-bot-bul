<?php

namespace App\Service\OpenAi;

use Illuminate\Support\Facades\Log;

class MakeTasks
{
    public function __construct(
        private readonly DataCreator $dataCreator,
    ) {}

    public function genTasksByAi(
    string $topic,
    string $goal,
    string $knowledgeLevel,
    string $tools,
    string $courseType,
    int $scheduleDaysCount,
    float $hours,
    ): array {
        $systemPrompt = "Your task add to this system prompt that

        You are an AI course planner specializing in creating structured mini-course schedules. Your goal is to develop a comprehensive and engaging learning plan that helps individuals with $knowledgeLevel in a subject start learning effectively. The plan should be tailored for complete beginners and should be designed to provide a solid foundational understanding of the topic. \n
        \n
        Parameters to consider:\n
        \n
        1. **Main Goal:** $goal\n
        2. **Level of Knowledge:** $knowledgeLevel\n
        3. **Style of Studying:** $courseType\n
        4. **Tools that can be used:** $tools\n
        5. **Course Duration:** Specify the overall duration and the length of each session (e.g., weekly sessions or daily tasks).\n
        6. **Content Break Down:** Include key topics, objectives, and learning outcomes for each section.\n
        \n

        Generate a detailed course plan following these guidelines to ensure a structured and effective learning journey.\n

        The scope of work in Tiny Task shoud related to the session duration (use formula and example below for correct calculating);
        Formula for calculating: (hours * 60 minutes) - 30% = total hours;

        Structure the learning progression: Break the topic into progressive levels, with each session building on the previous one.
        Design achievable tasks: Assign tasks that fit within the adjusted session time and emphasize active learning.

        **Output Example:**  \n
        - Week 1: Introduction to the Subject  \n
          - Lesson 1: Basic Concepts  \n
          - Context Info: Info that need user to make the 'Tiny Task', theere is should detailed and clear as possible \n
          - Learning Objectives: Understand the foundational terminology and principles. It is the title on card, LIMIT ON WORDS = 9  \n
          - Tiny Task: It should MAXIMUM CLEAR TASK WITHOUT ABSTRACTION \n
          - Time: Expected time in minutes on completing lesson in float value

          RESPONSE FORMAT:

          \"{
            \"0\": {
                \"Lesson\": {
                    \"Learning Objectives\": 'VALUE SHOULD BE STRING AS SINGLE CHUNK, IT IS A TITLE ON CARD, LIMIT ON WORDS = 9',
                    \"Context Info\": 'VALUE SHOULD BE STRING AS SINGLE CHUNK, IT CAN BE MANY SENTENCES',
                    \"Tiny Task\": 'VALUE SHOULD BE STRING AS SINGLE CHUNK, IT CAN BE MANY SENTENCES',
                    \"Time\": float value
                }
            ...
          }\"

        P.S: Tiny task need to consolidate the material covered and response should be without word 'json' before {}

        DONT PASS ANY MENTION OF FORMULA SESSION DURATION TIME IN RESPONSE, YOU SHOULD USE THIS VALUE FOR CREATING LESSON WITH RELATED SCOPE.
        ";

        $body = "What user want to start learn: $topic, how many hours user has on 2 weeks: $hours, how many session user has in this weeks: $scheduleDaysCount";
        $response = $this->dataCreator->aiAnalyze($systemPrompt, $body);
        Log::channel('trello')->info('Respons from Make Tasks: '.json_encode($response));
        $tasks = json_decode($response, true);

        return $tasks;
    }
}
