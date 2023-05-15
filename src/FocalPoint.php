<?php

namespace WpFocalPoint;

use Exception;

class FocalPoint {
    private static $instance;

    public static function Init(array $options = [
        "meta_key" => "_koli_wp_focal_point"
    ]) {
        if (!self::$instance) {
            self::$instance = new self($options);
        }

        return self::$instance;
    }

    public static function Instance() {
        return self::$instance;
    }

    private string $meta_key;

    public function meta_key():string {
        return $this->meta_key;
    }

    function __construct(array $options)
    {
        $this->meta_key = $options["meta_key"];
        add_filter( 'attachment_fields_to_edit', array( $this, 'attachment_fields_to_edit' ), 10, 2 );
        add_action( 'edit_attachment' , array( $this, 'edit_attachment'  ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts'  ) );
    }

    function admin_enqueue_scripts() {
        $theme_dir = realpath(get_template_directory());
        $directory = dirname( dirname(__FILE__) );
        if (str_starts_with($directory, $theme_dir)) {
            // Used within a theme
            $relative = str_replace($theme_dir, "", $directory);
            $url = get_template_directory_uri() . $relative;
        }
        else {
            throw new Exception("Support for use outside a theme has not yet been implemented.");
        }
        wp_enqueue_script( 'koli-wp-focal-point', $url . "/dist/wp-focal-point.umd.js", array(), filemtime($directory . "/dist/wp-focal-point.umd.js"), true );
        wp_enqueue_style( 'koli-wp-focal-point', $url . "/dist/style.css", array(), filemtime($directory . "/dist/style.css"));
    }

    function edit_attachment($attachment_id) {
        if( ! isset( $_REQUEST['attachments'] ) || ! isset( $_REQUEST['attachments'][$attachment_id] ) ) {
            return;
        }
        $attachment = $_REQUEST['attachments'][$attachment_id];
        $focal_point = array(
            'top'  => 0.5,
            'left' => 0.5
        );

        if ( isset( $attachment[$this->meta_key] ) ) {
            if ( isset( $attachment[$this->meta_key]['top'] ) ) {
                $focal_point['top']	= number_format( $attachment[$this->meta_key]['top'], 2 );
            }
            if ( isset( $attachment[$this->meta_key]['left' ] )) {
                $focal_point['left']	= number_format ($attachment[$this->meta_key]['left'], 2 );
            }

            update_post_meta( $attachment_id, $this->meta_key, $focal_point );
        }

    }
    

    function attachment_fields_to_edit($form_fields, $post) {
        if( substr($post->post_mime_type, 0, 5) == 'image' ) {
            $focal_point   = get_post_meta( $post->ID, $this->meta_key, true );
            if (!$focal_point) {
                $focal_point = [ 
                    "left" => 0.5,
                    "top" => 0.5
                 ];
            }

            $img_src = wp_get_attachment_image_src($post->ID, 'medium');
            $width = $img_src[1];
            $height = $img_src[2];            

            ob_start();
            ?>
            <div class="focal-point-preview" style="--ratio: <?= $height / $width?>;">
                <?php echo wp_get_attachment_image( $post->ID, 'full' ); ?>
                <div class="focal-point-preview__cursor"></div>
            </div>
            <?php
            $image = ob_get_clean();

            ob_start();
			?>
            <div class="focal-point">
                <div class="focal-point__value">
                <?= ($focal_point["left"] * 100) ?>% x <?= ($focal_point["top"] * 100) ?>%
                </div>
                <input type="hidden" class="koli_image_focus_left" name="attachments[<?= $post->ID; ?>][<?= $this->meta_key; ?>][left]" value="<?php echo $focal_point['left']; ?>" />
                <input type="hidden" class="koli_image_focus_top"  name="attachments[<?= $post->ID; ?>][<?= $this->meta_key; ?>][top]"  value="<?php echo $focal_point['top' ]; ?>" />
                <button type="button" class="button focal-point__button">Edit</button>
                <focal-point-modal class="focal-point-modal">
                    <div class="focal-point-modal__inner">
                        <div class="focal-point-modal__top">
                            <h2>Edit Focal Point</h2>
                        </div>
                        <div class="focal-point-modal__middle">
                            <?php echo $image ?>
                        </div>
                        <div class="focal-point-modal__bottom">
                            <button type="button" class="button focal-point-modal__btn-save">Save</button>
                        </div>                       
                    </div>
                </focal-point-modal>
            </div>

            <script>
                var event = new CustomEvent("koli_focal_point_init");
                window.dispatchEvent(event);
            </script>
            <?php
            $focal_point_html = ob_get_clean();
            $form_fields = array(
                'image_focal_point' => array(
                    'input' => 'html',
                    'label' => __( 'Focal Point' ),
                    'html'  => $focal_point_html
                )
            ) + $form_fields;
        }
        return $form_fields;
    }
}

/**
 * Returns the focal point of a given attachment.
 * 
 * 
 * @param mixed $attachment_id 
 * @return (float|string)[]
 * @throws Exception 
 */
function get_focal_point($attachment_id) {
    $instance = FocalPoint::Instance();
    if (!$instance) {
        throw new Exception("WpFocalPoint\FocalPoint has to be initialized before get_focal_point is used.");
    }
    $meta_key = $instance->meta_key();
    $focal = get_post_meta($attachment_id, $meta_key, true) ?? [];

    $left = floatval($focal["left"] ?? "0.5");
    $top = floatval($focal["top"] ?? "0.5");

    return [
        "left" => $left,
        "top" => $top,
        "leftPercentage" => ($left * 100) . '%',
        "topPercentage" => ($top * 100) . '%'
    ];
}