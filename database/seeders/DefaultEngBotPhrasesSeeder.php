<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultEngBotPhrasesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $phrases = [
            [
                'phrase_key' => 'lets_go',
                'language_code' => 'en',
                'phrase_text' => 'LET\'S GOOO'
            ],
            [
                'phrase_key' => 'welcome', 
                'language_code' => 'en',
                'phrase_text' => 'Hi, *:name!*'.
                            "\nIt **Schedule Bot** that helps you create a plan for your studying" .
                            "\nin a convenient Trello system. We will create a schedule plan for the next" .
                            "\n2 weeks." .
                            "\n*If you really want to improve yourself, push the button*"
            ],
            [
                'phrase_key' => 'subject_of_studies',
                'language_code' => 'en',
                'phrase_text' => 'Please tell me the name of your study subject!',
            ],
            [
                'phrase_key' => 'knowledge_level_question',
                'language_code' => 'en',
                'phrase_text' => "What level of knowledge do you have now?\nYou can have a massive answer, for example: beginner, advance, ULTRA HIGHT SENIOR",
            ],
            [
                'phrase_key' => 'total_hours_on_study',
                'language_code' => 'en',
                'phrase_text' => 'How many hours do you give yourself during these 2 weeks?' .
                                "\nPlease think about it as carefully as you can",
            ],
            [
                'phrase_key' => 'schedule',
                'language_code' => 'en',
                'phrase_text' => 'What schedule do you want to take for education?',
            ],
            [
                'phrase_key' => 'ask_email',
                'language_code' => 'en',
                'phrase_text' => 'Lastly, give me your email address. I will use it to create a workspace for you!',
            ],
            [
                'phrase_key' => 'validate_answer',
                'language_code' => 'en',
                'phrase_text' => 'Are you really sure about *:title*?',
            ],
            [
                'phrase_key' => 'tools_for_study',
                'language_code' => 'en',
                'phrase_text' => 'What tools you want use, or what tools have you heard about?',
            ],
            [
                'phrase_key' => 'trello_workspace_created',
                'language_code' => 'en',
                'phrase_text' => ':name, your Trello workspace for studying was successfully created🔥' .
                                 "\nPlease wait for a letter in your Gmail with the invite!",
            ],
            [
                'phrase_key' => 'error',
                'language_code' => 'en',
                'phrase_text' => 'Something went wrong, please wait while we work to fix it',
            ],
            [
                'phrase_key' => 'wrong_email',
                'language_code' => 'en',
                'phrase_text' => 'The :email has the wrong format, please enter the correct one 🚫',
            ],
            [
                'phrase_key' => 'wrong_hours',
                'language_code' => 'en',
                'phrase_text' => 'The :hours hours have the wrong format, please enter the correct one 🚫',
            ],
            [
                'phrase_key' => 'wrong_knowledge_level',
                'language_code' => 'en',
                'phrase_text' => 'Something wrong with your knowledge level 🚫',
            ],
            [
                'phrase_key' => 'wrong_tools_for_study',
                'language_code' => 'en',
                'phrase_text' => "Something wrong with your tools, maybe there are not exist or something else.\nPlease give others 👉🏻👈🏻",
            ],
            [
                'phrase_key' => 'course_type',
                'language_code' => 'en',
                'phrase_text' => 'What form of study process do you want?',
            ],
            [
                'phrase_key' => 'workspace_created',
                'language_code' => 'en',
                'phrase_text' => 'You already have a study workspace: :url',
            ],
            [
                'phrase_key' => 'wrong_subject_title',
                'language_code' => 'en',
                'phrase_text' => 'Could you please provide a more detailed subject title that you want to learn 👉🏻👈🏻',
            ],
            [
                'phrase_key' => 'wrong_learn_goal',
                'language_code' => 'en',
                'phrase_text' => 'Could you please provide a more detailed goal 👉🏻👈🏻',
            ],
            [
                'phrase_key' => 'goal_question',
                'language_code' => 'en',
                'phrase_text' => 'What your main goal in learning ?',
            ],
            [
                'phrase_key' => 'title_saved',
                'language_code' => 'en',
                'phrase_text' => 'Your title of object studies was save✅'
            ],
            [
                'phrase_key' => 'goal_saved',
                'language_code' => 'en',
                'phrase_text' => 'Your study goal was save✅'
            ],
            [
                'phrase_key' => 'knowledge_level_saved',
                'language_code' => 'en',
                'phrase_text' => 'Your knowledge level was save✅'
            ],
            [
                'phrase_key' => 'tools_saved',
                'language_code' => 'en',
                'phrase_text' => 'Your description of tools was save✅'
            ],
            [
                'phrase_key' => 'schedule_saved',
                'language_code' => 'en',
                'phrase_text' => 'Schedule was sucessufully save✅'
            ],
            [
                'phrase_key' => 'hours_saved',
                'language_code' => 'en',
                'phrase_text' => 'Hours on studying was sucessufully save✅'
            ],
            [
                'phrase_key' => 'course_type_saved',
                'language_code' => 'en',
                'phrase_text' => 'Form of study process was sucessufully save✅'
            ]
        ];

        DB::connection('mongodb')->table('bot_phrases')->insert($phrases);
    }
}
