# WP Focal Point Picker

*NOTE: Still WIP*

Composer installable (not a plugin) focal point picker for Wordpress.

This library does two things only:
1. It implements a UI for choosing the focal point of an image
2. It saves that focal point as a metafield on attachments

It does *not* concern itself with how to use those metafields when displaying the image.

One way of using the metafields is as such:
```php
<?php
$focal_point = \WpFocalPoint\get_focal_point($attachment); 
?>
<img style="object-position: <?= $focal_point["leftPercentage"] ?> <?= $focal_point["topPercentage"] ?>;" />
```