<?php
/** 评论区 */
if (!defined('ABSPATH')) exit;
if (post_password_required()) return;
?>
<div id="comments">
  <h2 style="font-size:18px">💬 用户评论 <span class="muted">（<?php echo get_comments_number(); ?>）</span></h2>

  <?php if (have_comments()): ?>
    <ol class="comment-list" style="list-style:none;padding:0;margin:16px 0">
      <?php wp_list_comments(['callback' => 'hahatool_comment', 'style' => 'ol']); ?>
    </ol>
  <?php else: ?>
    <p class="muted" style="margin:12px 0">还没有评论，来抢沙发～</p>
  <?php endif; ?>

  <?php
  comment_form([
    'class_form' => 'cform',
    'title_reply' => '',
    'comment_field' => '<label for="comment">说说你的使用体验 <span style="color:#f43f5e">*</span></label><textarea id="comment" name="comment" rows="3" required></textarea>',
    'fields' => [
      'author' => '<div class="grid2"><div><label for="author">昵称 <span style="color:#f43f5e">*</span></label><input id="author" name="author" required></div>',
      'email'  => '<div><label for="email">邮箱 <span style="color:#f43f5e">*</span>（不公开）</label><input id="email" name="email" type="email" required></div></div>',
    ],
    'submit_button' => '<button type="submit" class="btn" style="margin-top:14px">发表评论</button>',
    'comment_notes_before' => '',
    'comment_notes_after' => '',
  ]);
  ?>
</div>
