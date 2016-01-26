<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Symfony;

use CultuurNet\UDB3\BookingInfo;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Event\EventEditingServiceInterface;
use CultuurNet\UDB3\Media\MediaManagerInterface;
use CultuurNet\UDB3\Media\MediaObject;
use CultuurNet\UDB3\Place\PlaceEditingServiceInterface;
use CultuurNet\UDB3\Timestamp;
use Drupal\Core\Site\Settings;
use Drupal\image\Entity\ImageStyle;
use Drupal\file\Entity\File;
use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String;

/**
 * Base class for offer reset callbacks.
 */
abstract class OfferRestBaseController
{
    /**
     * TODO: Create a shared interface for event and places
     * @var EventEditingServiceInterface|PlaceEditingServiceInterface
     */
    protected $editor;

    /**
     * @var MediaManagerInterface
     */
    protected $mediaManager;

    /**
     * Update the description property.
     *
     * @param Request $request
     * @param string $cdbid
     * @return JsonResponse
     */
    public function updateDescription(Request $request, $cdbid)
    {
        $response = new JsonResponse();
        $body_content = json_decode($request->getContent());

        if (!isset($body_content->description) || $body_content->description == '') {
            return new JsonResponse(['error' => "description required"], 400);
        }

        $command_id = $this->editor->updateDescription(
            $cdbid,
            $body_content->description
        );

        $response->setData(['commandId' => $command_id]);

        return $response;
    }

    /**
     * Update the typicalAgeRange property.
     *
     * @param Request $request
     * @param string $cdbid
     * @return JsonResponse
     */
    public function updateTypicalAgeRange(Request $request, $cdbid)
    {
        $body_content = json_decode($request->getContent());
        if (empty($body_content->typicalAgeRange)) {
            return new JsonResponse(['error' => "typicalAgeRange required"], 400);
        }

        $response = new JsonResponse();

        $command_id = $this->editor->updateTypicalAgeRange($cdbid, $body_content->typicalAgeRange);
        $response->setData(['commandId' => $command_id]);

        return $response;
    }

    /**
     * Delete the typicalAgeRange property.
     *
     * @param Request $request
     * @param string $cdbid
     * @return JsonResponse
     */
    public function deleteTypicalAgeRange(Request $request, $cdbid)
    {
        $response = new JsonResponse();

        $command_id = $this->editor->deleteTypicalAgeRange($cdbid);
        $response->setData(['commandId' => $command_id]);

        return $response;
    }

    /**
     * Update the organizer property.
     *
     * @param Request $request
     * @param string $cdbid
     * @return JsonResponse
     */
    public function updateOrganizer(Request $request, $cdbid)
    {
        $body_content = json_decode($request->getContent());
        if (empty($body_content->organizer)) {
            return new JsonResponse(['error' => "organizer required"], 400);
        }

        $response = new JsonResponse();

        $command_id = $this->editor->updateOrganizer($cdbid, $body_content->organizer);
        $response->setData(['commandId' => $command_id]);

        return $response;
    }

    /**
     * Delete the given organizer.
     *
     * @param string $cdbid
     * @param string $organizerId
     * @return JsonResponse
     */
    public function deleteOrganizer($cdbid, $organizerId)
    {
        $response = new JsonResponse();

        $command_id = $this->editor->deleteOrganizer($cdbid, $organizerId);
        $response->setData(['commandId' => $command_id]);

        return $response;
    }

    /**
     * Update the contactPoint.
     *
     * @param Request $request
     * @param string $cdbid
     * @return JsonResponse
     */
    public function updateContactPoint(Request $request, $cdbid)
    {
        $body_content = json_decode($request->getContent());
        if (empty($body_content->contactPoint) ||
            !isset($body_content->contactPoint->url) ||
            !isset($body_content->contactPoint->email) ||
            !isset($body_content->contactPoint->phone)) {
            return new JsonResponse(['error' => "contactPoint and his properties required"], 400);
        }

        $response = new JsonResponse();

        $command_id = $this->editor->updateContactPoint(
            $cdbid,
            new ContactPoint(
                $body_content->contactPoint->phone,
                $body_content->contactPoint->email,
                $body_content->contactPoint->url
            )
        );
        $response->setData(['commandId' => $command_id]);

        return $response;
    }

    /**
     * Update the bookingInfo.
     *
     * @param Request $request
     * @param string $cdbid
     * @return JsonResponse
     */
    public function updateBookingInfo(Request $request, $cdbid)
    {
        $body_content = json_decode($request->getContent());
        if (empty($body_content->bookingInfo)) {
            return new JsonResponse(['error' => "bookingInfo required"], 400);
        }

        $response = new JsonResponse();

        $data = $body_content->bookingInfo;
        $bookingInfo = new BookingInfo(
            $data->url,
            $data->urlLabel,
            $data->phone,
            $data->email,
            isset($data->availabilityStarts) ? $data->availabilityStarts : '',
            isset($data->availabilityEnds) ? $data->availabilityEnds : ''
        );
        $command_id = $this->editor->updateBookingInfo($cdbid, $bookingInfo);
        $response->setData(['commandId' => $command_id]);

        return $response;
    }

    /**
     * Update the facilities.
     *
     * @param Request $request
     * @param string $cdbid
     * @return JsonResponse
     */
    public function updateFacilities(Request $request, $cdbid)
    {
        $body_content = json_decode($request->getContent());
        if (empty($body_content->facilities)) {
            return new JsonResponse(['error' => "facilities required"], 400);
        }

        $response = new JsonResponse();

        $command_id = $this->editor->updateFacilities($cdbid, $body_content->facilities);
        $response->setData(['commandId' => $command_id]);

        return $response;
    }

    /**
     * Add an image.
     *
     * @param Request $request
     * @param string $eventId
     */
    public function addImage(Request $request, $eventId)
    {
        $body_content = json_decode($request->getContent());
        if (empty($body_content->mediaObjectId)) {
            return new JsonResponse(['error' => "media object id required"], 400);
        }

        $mediaObjectId = new UUID($body_content->mediaObjectId);

        $image = $this->mediaManager->getImage($mediaObjectId);

        $response = new JsonResponse();
        $commandId = $this->editor->addImage($eventId, $image);
        $response->setData(['commandId' => $commandId]);

        return $response;
    }

    /**
     * Update an image.
     *
     * @param Request $request
     * @param string $eventId
     * @param string $mediaObjectId
     */
    public function updateImage(Request $request, $eventId, $mediaObjectId)
    {
        $body_content = json_decode($request->getContent());
        $description = new String($body_content->description);
        $copyrightHolder = new String($body_content->copyrightHolder);
        $imageId = new UUID($mediaObjectId);
        $image = $this->mediaManager->getImage($imageId);

        $commandId = $this->editor->updateImage(
            $eventId,
            $image,
            $description,
            $copyrightHolder
        );

        $response = new JsonResponse();
        $response->setData(['commandId' => $commandId]);

        return $response;
    }

    /**
     * Delete an image.
     *
     * @param Request $request
     * @param string $cdbid
     * @param string $index
     */
    public function deleteImage($cdbid, $index)
    {
        $itemJson = $this->getItem($cdbid);
        $item = json_decode($itemJson);
        if (!isset($item->mediaObject[$index])) {
            return new JsonResponse(['error' => "The image to edit was not found"], 400);
        }

        // Get the fid of the old file.
        $url = $item->mediaObject[$index]->url;
        $old_fid = $this->getFileIdByUrl($url);

        $response = new JsonResponse();
        $command_id = $this->editor->deleteImage($cdbid, $index, $old_fid);
        $response->setData(['commandId' => $command_id]);

        return $response;
    }

    /**
     * Save the uploaded image to the destination folder.
     */
    protected function saveUploadedImage(UploadedFile $file, $itemId, $destination)
    {
        $filename = $file->getClientOriginalName();

        // Save the image in drupal files.
        file_prepare_directory($destination, FILE_CREATE_DIRECTORY);

        $file = file_save_data(
            file_get_contents($file->getPathname()),
            $destination . '/' . $filename,
            FILE_EXISTS_RENAME
        );

        $this->fileUsage->add($file, 'culturefeed_udb3', 'udb3_item', $itemId);

        return $file;
    }

    /**
     * Get the file id of a given url.
     */
    protected function getFileIdByUrl($url)
    {
        $public_files_path = Settings::get('file_public_path', conf_path() . '/files');
        $uri = str_replace($GLOBALS['base_url'] . '/' . $public_files_path . '/', 'public://', $url);

        return db_query('SELECT fid FROM {file_managed} WHERE uri = :uri', array(':uri' => $uri))->fetchField();
    }
}
