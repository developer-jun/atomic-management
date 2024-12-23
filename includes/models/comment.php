<?php

namespace Domain\Models;

class Comment {
  public function __construct() {

  }



  public function getComments($post_id) {    
    // Retrieve comments for the specified task
    $comments = get_comments(array(
        'post_id' => $post_id,
        'status' => 'approve',
    ));

    $comments_array = array();
    foreach ($comments as $comment) {
        $comments_array[] = array(
            'author' => $comment->comment_author,
            'content' => $comment->comment_content,
            'date' => $comment->comment_date,
        );
    }

    return $comments_array;
  }


  public function getComment() {
    
  }

}