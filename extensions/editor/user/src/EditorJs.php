<?php

namespace EditorUser;

class EditorJs
{
	public static function extractEmployeeTaskBlocks($content) {
		$content = json_decode($content);
		$blocks = array_filter($content->blocks, function ($block) {
			return $block->type === 'checklist';
		});

		return $blocks;
	}

	public static function extractEmployeeTaskStatus($blocks) {
		$tasks = [];
		foreach ($blocks as $block) {
			foreach ($block->data->items as $item) {
				$empIds = [];
				preg_match('/\( id.*?\)/', $item->user_name, $empIds);
				if (empty($empIds)) {
					continue;
				}
				$empId = $empIds[0];
				$empId = str_replace('( id:', '', $empId);
				$empId = str_replace(' )', '', $empId);
				$empId = (int)$empId;
				if (empty($empId)) {
					continue;
				}
				if (!isset($tasks[$empId])) {
					$tasks[$empId] = true;
				}
				$tasks[$empId] = $tasks[$empId] && (boolean)$item->checked;
			}
		}

		return $tasks;
	}

	public static function extractQuizBlocks($content) {
		$content = json_decode($content);
		$quizzes = array_filter($content->blocks, function ($block) {
			return $block->type === 'quiz';
		});

		return $quizzes;
	}

	public static function extractQuizBlockAnswers($content) {
		$content = json_decode($content);
		$quizzes = array_filter($content->blocks, function ($block) {
			return $block->type === 'quiz';
		});

		$answers = [];
		foreach ($quizzes as $block) {
			$answers[] = [
				'id' => $block->id,
				'answers' => $block->data->answers,
			];
		}
		return $answers;
	}

	public static function compareQuizAnswers($correctAnswers, $answer) {
		$quizAnswers = array_filter($correctAnswers, function ($item){
			return ( $item->type === 'quiz' );
		});

		if (empty($quizAnswers)) {
			return false;
		}

		$quizAnswers = end($quizAnswers)->answers;
		$answerId = $answer->id;

		$correctAnswer = array_filter($quizAnswers, function ($item) use ($answerId) {
			return $answerId === $item->id;
		});

		if (empty($correctAnswer)) {
			return false;
		}
		$correctAnswer = end($correctAnswer);

		if (!is_array($correctAnswer->answers) || !is_array($answer->selectedVariants)) {
			return false;
		}

		sort($correctAnswer->answers);
		sort($answer->selectedVariants);

		return $correctAnswer->answers === $answer->selectedVariants;
	}

	public static function updateQuizAnswers($contentStr, $answers) {
		$updated = false;
		$content = json_decode($contentStr);
		foreach ($content->blocks as $key => $block) {
			if ($content->blocks[$key]->type === 'quiz' && $content->blocks[$key]->id === $answers->id) {
				$content->blocks[$key]->data->answers = $answers->selectedVariants;
				$updated = true;
			}
		}

		if (!$updated) {
			return $contentStr;
		}

		return json_encode($content);
	}

	public static function removeQuizAnswers($contentStr) {
		$updated = false;
		$content = json_decode($contentStr);
		foreach ($content->blocks as $key => $block) {
			if ($content->blocks[$key]->type === 'quiz') {
				$content->blocks[$key]->data->answers = [];
				$updated = true;
			}
		}

		if (!$updated) {
			return $contentStr;
		}

		return json_encode($content);
	}
}
