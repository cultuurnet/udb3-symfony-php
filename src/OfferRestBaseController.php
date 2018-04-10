<?php

namespace CultuurNet\UDB3\Symfony;

use CultuurNet\Deserializer\JSONDeserializer;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Event\EventEditingServiceInterface;
use CultuurNet\UDB3\Media\MediaManagerInterface;
use CultuurNet\UDB3\Offer\AgeRange;
use CultuurNet\UDB3\Offer\OfferEditingServiceInterface;
use CultuurNet\UDB3\Place\PlaceEditingServiceInterface;
use CultuurNet\UDB3\Symfony\Deserializer\BookingInfo\BookingInfoJSONDeserializer;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;

/**
 * Base class for offer reset callbacks.
 */
abstract class OfferRestBaseController
{
    /**
     * TODO: Create a shared interface for event and places
     * @var EventEditingServiceInterface|PlaceEditingServiceInterface|OfferEditingServiceInterface
     */
    protected $editor;

    /**
     * @var MediaManagerInterface
     */
    protected $mediaManager;

    /**
     * @var BookingInfoJSONDeserializer
     */
    private $bookingInfoDeserializer;

    /**
     * OfferRestBaseController constructor.
     * @param EventEditingServiceInterface|PlaceEditingServiceInterface $editor
     * @param MediaManagerInterface $mediaManager
     * @param JSONDeserializer $bookingInfoDeserializer
     */
    public function __construct(
        $editor,
        MediaManagerInterface $mediaManager,
        JSONDeserializer $bookingInfoDeserializer = null
    ) {
        $this->editor = $editor;
        $this->mediaManager = $mediaManager;

        if (!$bookingInfoDeserializer) {
            $bookingInfoDeserializer = new BookingInfoJSONDeserializer();
        }
        $this->bookingInfoDeserializer = $bookingInfoDeserializer;
    }

    /**
     * Update the description property.
     *
     * @todo The call to OfferEditingServiceInterface::updateDescription() is broken. Is this still used?
     *
     * @param Request $request
     * @param string $cdbid
     * @return JsonResponse
     */
    public function updateDescription(Request $request, $cdbid)
    {
        $response = new JsonResponse();
        $body_content = json_decode($request->getContent());

        if (!isset($body_content->description)) {
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

        // @todo Use a data validator and change to an exception so it can be converted to an API problem
        if (empty($body_content->typicalAgeRange)) {
            return new JsonResponse(['error' => "typicalAgeRange required"], 400);
        }

        $response = new JsonResponse();
        $ageRange = AgeRange::fromString($body_content->typicalAgeRange);

        $command_id = $this->editor->updateTypicalAgeRange($cdbid, $ageRange);
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
     * @param string $cdbid
     * @param string $organizerId
     * @return JsonResponse
     */
    public function updateOrganizer($cdbid, $organizerId)
    {
        $response = new JsonResponse();

        $command_id = $this->editor->updateOrganizer($cdbid, $organizerId);
        $response->setData(['commandId' => $command_id]);

        return $response;
    }

    /**
     * @deprecated
     *
     * @param Request $request
     * @param string $cdbid
     * @return JsonResponse
     */
    public function updateOrganizerFromJsonBody(Request $request, $cdbid)
    {
        $body_content = json_decode($request->getContent());

        // @todo Use a data validator and change to an exception so it can be converted to an API problem
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

        // @todo Use a data validator and change to an exception so it can be converted to an API problem
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
        $body = (string) $request->getContent();
        $bookingInfo = $this->bookingInfoDeserializer->deserialize(new StringLiteral($body));

        $command_id = $this->editor->updateBookingInfo($cdbid, $bookingInfo);

        return (new JsonResponse())
            ->setData(['commandId' => $command_id]);
    }

    /**
     * Add an image.
     *
     * @param Request $request
     * @param string $itemId
     */
    public function addImage(Request $request, $itemId)
    {
        $body_content = json_decode($request->getContent());
        if (empty($body_content->mediaObjectId)) {
            return new JsonResponse(['error' => "media object id required"], 400);
        }

        // @todo Validate that this id exists and is in fact an image and not a different type of media object
        $imageId = new UUID($body_content->mediaObjectId);

        $response = new JsonResponse();
        $commandId = $this->editor->addImage($itemId, $imageId);
        $response->setData(['commandId' => $commandId]);

        return $response;
    }

    /**
     * Select the main image for an item.
     *
     * @param Request $request
     * @param string $itemId
     */
    public function selectMainImage(Request $request, $itemId)
    {
        $body_content = json_decode($request->getContent());
        if (empty($body_content->mediaObjectId)) {
            return new JsonResponse(['error' => "media object id required"], 400);
        }

        $mediaObjectId = new UUID($body_content->mediaObjectId);

        // @todo MediaManagerInterface has no getImage() method.
        // Also, can we be sure that the given $mediaObjectId points to an image and not a different type?
        $image = $this->mediaManager->getImage($mediaObjectId);

        $response = new JsonResponse();
        $commandId = $this->editor->selectMainImage($itemId, $image);
        $response->setData(['commandId' => $commandId]);

        return $response;
    }

    /**
     * Update an image.
     *
     * @param Request $request
     * @param string $itemId
     * @param string $mediaObjectId
     */
    public function updateImage(Request $request, $itemId, $mediaObjectId)
    {
        $body_content = json_decode($request->getContent());
        $description = new StringLiteral($body_content->description);
        $copyrightHolder = new StringLiteral($body_content->copyrightHolder);
        $imageId = new UUID($mediaObjectId);

        // @todo MediaManagerInterface has no getImage() method.
        // Also, can we be sure that the given $mediaObjectId points to an image and not a different type?
        $image = $this->mediaManager->getImage($imageId);

        $commandId = $this->editor->updateImage(
            $itemId,
            $image,
            $description,
            $copyrightHolder
        );

        $response = new JsonResponse();
        $response->setData(['commandId' => $commandId]);

        return $response;
    }

    /**
     * Remove an image from an item by id.
     *
     * @param Request $request
     * @param string $itemId
     * @param string $mediaObjectId
     */
    public function removeImage($itemId, $mediaObjectId)
    {
        $imageId = new UUID($mediaObjectId);

        // @todo MediaManagerInterface has no getImage() method.
        // Also, can we be sure that the given $mediaObjectId points to an image and not a different type?
        $image = $this->mediaManager->getImage($imageId);

        $command_id = $this->editor->removeImage($itemId, $image);

        return new JsonResponse(['commandId' => $command_id]);
    }

    /**
     * Save the uploaded image to the destination folder.
     *
     * @todo Seems unused, remove? Uses D8-specific functions.
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
     *
     * @todo Seems unused, remove? Uses D8-specific functions.
     */
    protected function getFileIdByUrl($url)
    {
        $public_files_path = Settings::get('file_public_path', conf_path() . '/files');
        $uri = str_replace($GLOBALS['base_url'] . '/' . $public_files_path . '/', 'public://', $url);

        return db_query('SELECT fid FROM {file_managed} WHERE uri = :uri', array(':uri' => $uri))->fetchField();
    }
}
