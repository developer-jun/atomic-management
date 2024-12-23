<?php
namespace Domain;

class TaskCommentMetabox {

  public function __construct() {
    
  }

  public function renderCommentMetabox() {
    ?>
    <div id="comments-section">
        <h3>Comments</h3>
        <div id="comment-list">
            <!-- Existing comments will be appended here -->
        </div>
        <form id="commentForm">
            <label for="commentAuthor">Name</label>
            <input type="text" id="commentAuthor" name="commentAuthor" required>
            
            <label for="commentText">Comment</label>
            <textarea id="commentText" name="commentText" required></textarea>
            
            <button type="submit">Submit Comment</button>
        </form>
    </div>
    <?php
  }
}