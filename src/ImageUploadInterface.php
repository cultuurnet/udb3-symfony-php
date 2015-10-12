<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Symfony;

/**
 * Interface for controllers that can upload images.
 */
interface ImageUploadInterface {

    /**
     * Get the destination dir for the image.
     */
    public function getImageDestination($id);

    /**
     * Get the item.
     */
    public function getItem($id);

}
