<?php
/**
 * Block template file: block.php
 *
 * Landing Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

// Create id attribute allowing for custom "anchor" value.
$id = 'landing-' . $block['id'];
if ( ! empty($block['anchor'] ) ) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$classes = 'acf-block block-landing';
if ( ! empty( $block['className'] ) ) {
    $classes .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
    $classes .= ' align' . $block['align'];
}

?>

<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $classes ); ?>">
    
    <div class="text-center mb-10">
        <img src="<?php echo assets_url('/dist/images/sign.png'); ?>" class="w-full" />
    </div>

    <div class="container text-center prose">
        
        <h3 class="text-3xl text-info mb-5">
            Can I Park Here?
        </h3>

        <p class="text-xl mb-10">
            Snap a photo of a parking sign to find out if you can park there now. 
        </p>
    </div>

    <div class="fixed bottom-[20px] left-0 w-full">
        <div class="container">
            <a class="btn btn-primary btn-lg w-full text-2xl rounded-none" href="/camera">Try It Now</a>
        </div>
    </div>

</div>