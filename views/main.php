<?php

if (isset($_POST['import_posts'])) {
  $this->import_posts();
} ?>

<div class="wrap">
  <h1>Import Posts</h1>
  <form method="post">
    <div class="submit">
      <input type="submit" name="import_posts" id="import_posts" class="button button-primary" value="Import Posts">
    </div>
  </form>
</div>