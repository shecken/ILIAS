<?php
require_once 'Services/VIWIS/interfaces/interface.QuestionXMLCreator.php';
require_once 'Services/VIWIS/exceptions/class.QuestionException.php';
require_once 'Services/VIWIS/classes/class.QuestionTypes.php';
require_once 'Services/VIWIS/classes/class.GenericFeedback.php';
require_once 'Modules/TestQuestionPool/classes/class.assSingleChoice.php';
require_once 'Modules/TestQuestionPool/classes/class.assMultipleChoice.php';
require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssSingleChoiceFeedback.php';
require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssMultipleChoiceFeedback.php';

/**
 *	@inheritdoc
 */
class IliasQuestionXMLCreator implements QuestionXMLCreator {

	private $title;
	private $id;
	private $question;
	private $type;
	private $answers = array();
	private $correct_answers = array();
	private $generic_feedback;

	/**
	 *	@inheritdoc
	 */
	public function XML() {
		if(count($this->answers) === 0) {
			throw new QuestionException("No Answers Defined");
		}
		if(count($this->correct_answers) === 0) {
			throw new QuestionException("No Correct Answers Defined");
		}
		global $ilCtrl,$ilDB,$lng;
		switch($this->type) {
			case 'single':
				$obj = new assSingleChoice(		$this->title,
												'',
												'root user',
												-1,
												$this->question);
				
				$points_per_ans = array();
				foreach ($this->correct_answers as $correct_answers_id) {
					$points_per_ans[$correct_answers_id] = 1;
				}
				break;
			case 'multiple':
				$obj = new assMultipleChoice(	$this->title,
												'',
												'root user',
												-1,
												$this->question);
				$cnt_correct_answers = count($this->correct_answers);
				$step = 0;
				/**
				 *	We would like to assign 1 point to any question.
				 *	For multiple select type questions this means that
				 *	we must distribute fractional points among several right
				 *	answers. If we chose a constant distribution, this would cause
				 *	problems due to rounding-errors in floats, for instance in case of
				 *	3 right answers. To avoid this, we pick a different distribution, where
				 *	the floating point representation of points for any right answer is
				 *	exact and thus the sum remains 1.
				 */
				while(($cnt_correct_answers + $step) % 3 === 0 || ($cnt_correct_answers + $step) % 7 === 0) { 	//in principle we should continue to exclude all primes > 10
					$step++;																//for now we will assume that there are no questions having more											//than 10 correct answers.
				}
				$pnts = 1/($cnt_correct_answers + $step);
				$cnt = 0;
				foreach ($this->correct_answers as $correct_answers_id) {
					$points_per_ans[$correct_answers_id] = ($cnt === 0) ? (1 + $step) * $pnts : $pnts;
					$cnt++;
				}
				break;
			default:
				throw new QuestionException("Unknown Question Type ".$this->type);
		}
		$obj->feedbackOBJ = new GenericFeedback();
		$obj->feedbackOBJ->setGenericFeedback($this->generic_feedback);
		$obj->setId($this->id);

		foreach ($this->answers as $answer_id => $answer) {
			$points = in_array($answer_id, $this->correct_answers) ? $points_per_ans[$answer_id] : -1;
			$obj->addAnswer($answer,$points);
		}
		return $obj->toXML();
	}

	/**
	 *	@inheritdoc
	 */
	public function setTitle($title) {
		if($title) {
			$this->title = $title;
			return $this;
		}
		throw new QuestionException("Invalid Title");
	}

	/**
	 *	@inheritdoc
	 */
	public function setId($id) {
		if($id) {
			$this->id = $id;
			return $this;
		}
		throw new QuestionException("Invalid Id");
	}

	/**
	 *	@inheritdoc
	 */
	public function setQuestion($question) {
		if($question) {
			$this->question = $question;
			return $this;
		}
		throw new QuestionException("Empty Question");
	}

	/**
	 *	@inheritdoc
	 */
	public function addAnswer($answer, $correct) {
		if($answer) {
			$this->answers[md5($answer)] = $answer;
			if($correct) {
				$this->correct_answers[] = md5($answer);
			}
			return $this;
		}
		throw new QuestionException("Invalid Answer");
	}

	/**
	 *	@inheritdoc
	 */
	public function setType($question_type) {
		if(QuestionTypes::validType($question_type)) {
			$this->type = $question_type;
			return $this;
		}
		throw new QuestionException("Unknown Question Type");
	}

	/**
	 *	@inheritdoc
	 */
	public function setGenericFeedback($generic_feedback) {
		if($generic_feedback) {
			$this->generic_feedback = $generic_feedback;
			return $this;
		}
		throw new QuestionException("No Generic Feedback Given");
	} 

}