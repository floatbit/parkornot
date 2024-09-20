<?php get_header(); ?>

<?php
    $chatgpt_response = get_field('chatgpt_response');
?>

<?php if (has_post_thumbnail()): ?>
    <div class="mb-10">
        <?php the_post_thumbnail('full', ['class' => 'w-full h-auto shadow-lg']); ?>
    </div>
<?php endif; ?>

<div class="container prose">
    <p class="font-bold text-xl">
        <?php 
            $time = get_field('time');
            $formatted_time = date('g:i A', strtotime($time));
        ?>
        <?php print get_field('day');?> <?php print get_field('date');?> <?php print $formatted_time;?>
    </p>
    <p class="text-bold text-xl">
        <?php print $chatgpt_response;?>
    </p>

    <p class="mt-10">
        <a class="btn btn-primary btn-lg w-full text-2xl rounded-none" href="/camera">Take another photo</a>
    </p>
</div>


<?php if (isset($_GET['c'])) pr($json); ?>


<?php get_footer(); ?>