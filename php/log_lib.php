<?php

require_once('credentials.php');

// TODO: remove - just for debugging purpose
ini_set('display_errors', '1');
error_reporting(E_ALL);

define("EMPTY","---");
define("SPLIT","|");

function generateQuestions($construction_ids, $construction_questions, $id){
  global $host, $db, $username, $pw;

  $questionnaire = true;
  if($id != -1){
    $questionnaire = false;
  }

  $tmp = '';

  $questions = '';
  if(sizeof($construction_questions) == 1 && !is_array($construction_questions)){
    $questions = '(';
    $questions .= str_replace(constant("SPLIT"), ",", $construction_questions);
    $questions .= ')';
  }else{
    $questions = '(';
    for($i = 0; $i < sizeof($construction_questions); $i++){
      $questions .= str_replace(constant("SPLIT"), ",", $construction_questions[$i]);
      if($i != sizeof($construction_questions) - 1){
        $questions .= ',';
      }
    }
    $questions .= ')';
}

  $link = mysqli_connect($host, $username, $pw, $db) or die('Error: ' . mysqli_error());

  $query = 'SELECT * FROM questions WHERE id in ' . $questions . ' AND active="1"';
  $result = mysqli_query($link, $query) or die(mysqli_error($link));

  $question_id = array();
  $question_text = array();
  $question_answers = array();
  $question_images = array();

  while ($row = mysqli_fetch_object($result)) {
    $question_id[] = $row -> id;
    $question_text[] = $row -> question;
    $question_answers[] = $row -> answers;
    $question_images[] = $row -> photo;
  }

  $counter = 1;
  // iterate through all constructions
  for($i = 0; $i < sizeof($construction_questions); $i++){
    $con_que = $construction_questions[$i];
    if(!$questionnaire){
      $con_que = $construction_questions;
    }

    // iterate through all questions
    $constructions_questions_arr = explode(constant("SPLIT"), $con_que);
    $construction_data = $i + 1;
    if(!$questionnaire){
      $construction_data = $id;
    }
    for($j = 0; $j < sizeof($constructions_questions_arr); $j++){
      $construction_question_id = array_search($constructions_questions_arr[$j], $question_id);
      $question_answers_arr = explode(constant("SPLIT"), $question_answers[$construction_question_id]);
      $tmp .= '<div class="row question" data-construction="' . $construction_data . '" data-id="' . $construction_ids[$i] . '">
                  <div class="col-md-12 padding-0">
                    <img class="center-block img-responsive" src="../img/' . $question_images[$construction_question_id] .'" />
                  </div>    
                  <div class="col-md-12">
                    <h3>
                      Frage ' . $counter . ': ' . $question_text[$construction_question_id] . '
                    </h3>
                    <button type="button" class="col-xs-12 btn question-answer yes">' . $question_answers_arr[0] . '</button>
                    <button type="button" class="col-xs-12 btn question-answer no">' . $question_answers_arr[1] . '</button>
                    <div class="textfield">
                      <textarea name="description" class="form-control" rows="5" placeholder="Was stimmt nicht?" id="no-description"></textarea>
                    </div>
                  </div>
                </div>';
      $counter++;
    }
  }
  mysqli_close($link);
  return $tmp;
}

function generateMenu($names, $id){
  $tmp = '';
  $size = sizeof($names);
  if($id != -1){
    $size = 1;
  }
  for($i = 0; $i < $size; $i++){
    $number = $i + 1;
    $name = $names[$i];
    if($id != -1){
      $number = $id;
      $name = $names;
    }
    $tmp .= '<li><a href="#" data-construction="' . $number .'" class="construction_link">BW' . $number . ': ' . $name . '</a></li>';               
  }

  $tmp .= '<li><a href="#" data-construction="0" class="construction_link">Abschließen</a></li>';
  return $tmp;
}

function resetConstruction($id){
  global $host, $db, $username, $pw;

  $link = mysqli_connect($host, $username, $pw, $db) or die('Error: ' . mysqli_error());
  $reset = "UPDATE constructions SET last_checked=null, last_controller='', last_comment='" . constant("EMPTY") . "', maintain_checked=null, maintain_controller='', maintain_comment='" . constant("EMPTY") . "', authorize_checked=null, authorize_controller='', authorize_comment='" . constant("EMPTY") . "' WHERE id=" . $id;
  echo 'RESETTING: ' . $reset;
  mysqli_query($link, $reset);
  mysqli_close($link);
}

function getQuestionText($id){
  global $host, $db, $username, $pw;

  $link = mysqli_connect($host, $username, $pw, $db) or die('Error: ' . mysqli_error());
  $question_text = '';
  $query = "SELECT question FROM questions WHERE id=" . $id;
  echo 'question_query: ' . $query;
  $result = mysqli_query($link, $query) or die(mysqli_error($link));
  while ($row = mysqli_fetch_object($result)) {
    $question_text = $row -> question;
  }
  mysqli_close($link);
  return $question_text;
}

?>