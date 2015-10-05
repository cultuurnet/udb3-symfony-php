<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Symfony;

use CultuurNet\UDB3\BookingInfo;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\MediaObject;
use CultuurNet\UDB3\Timestamp;
use Drupal\Core\Site\Settings;
use Drupal\image\Entity\ImageStyle;
use Drupal\file\Entity\File;
use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base class for offer reset callbacks.
 */
abstract class OfferRestBaseController
{
    protected $editor;

    /**
     * Update the description property.
     *
     * @param Request $request
     * @param string $cdbid
     * @return JsonResponse
     */
    public function updateDescription(Request $request, $cdbid) {

        $response = new JsonResponse();
        $body_content = json_decode($request->getContent());

        if (!$body_content->description) {
            return new JsonResponse(['error' => "description required"], 400);
        }

        try {

            $command_id = $this->editor->updateDescription(
                $cdbid,
                $body_content->description
            );

            $response->setData(['commandId' => $command_id]);
        } catch (Exception $e) {
            $response->setStatusCode(400);
            $response->setData(['error' => $e->getMessage()]);
            watchdog_exception('udb3', $e);
        }

        return $response;

    }

    /**
     * Update the typicalAgeRange property.
     *
     * @param Request $request
     * @param string $cdbid
     * @return JsonResponse
     */
    public function updateTypicalAgeRange(Request $request, $cdbid) {

        $body_content = json_decode($request->getContent());
        if (empty($body_content->typicalAgeRange)) {
            return new JsonResponse(['error' => "typicalAgeRange required"], 400);
        }

        $response = new JsonResponse();
        try {
            $command_id = $this->editor->updateTypicalAgeRange($cdbid, $body_content->typicalAgeRange);
            $response->setData(['commandId' => $command_id]);
        }
        catch (Exception $e) {
            $response->setStatusCode(400);
            $response->setData(['error' => $e->getMessage()]);
            watchdog_exception('udb3', $e);
        }

        return $response;

    }

    /**
     * Delete the typicalAgeRange property.
     *
     * @param Request $request
     * @param string $cdbid
     * @return JsonResponse
     */
    public function deleteTypicalAgeRange(Request $request, $cdbid) {

        $response = new JsonResponse();
        try {
            $command_id = $this->editor->deleteTypicalAgeRange($cdbid);
            $response->setData(['commandId' => $command_id]);
        }
        catch (Exception $e) {
            $response->setStatusCode(400);
            $response->setData(['error' => $e->getMessage()]);
            watchdog_exception('udb3', $e);
        }

        return $response;

    }

    /**
     * Update the organizer property.
     *
     * @param Request $request
     * @param string $cdbid
     * @return JsonResponse
     */
    public function updateOrganizer(Request $request, $cdbid) {

        $body_content = json_decode($request->getContent());
        if (empty($body_content->organizer)) {
            return new JsonResponse(['error' => "organizer required"], 400);
        }

        $response = new JsonResponse();
        try {
            $command_id = $this->editor->updateOrganizer($cdbid, $body_content->organizer);
            $response->setData(['commandId' => $command_id]);
        }
        catch (Exception $e) {
            $response->setStatusCode(400);
            $response->setData(['error' => $e->getMessage()]);
            watchdog_exception('udb3', $e);
        }

        return $response;

    }

    /**
     * Delete the given organizer.
     *
     * @param string $cdbid
     * @param string $organizerId
     * @return JsonResponse
     */
    public function deleteOrganizer($cdbid, $organizerId) {

        $response = new JsonResponse();
        try {
            $command_id = $this->editor->deleteOrganizer($cdbid, $organizerId);
            $response->setData(['commandId' => $command_id]);
        }
        catch (Exception $e) {
            $response->setStatusCode(400);
            $response->setData(['error' => $e->getMessage()]);
            watchdog_exception('udb3', $e);
        }

        return $response;

    }

    /**
     * Update the contactPoint.
     *
     * @param Request $request
     * @param string $cdbid
     * @return JsonResponse
     */
    public function updateContactPoint(Request $request, $cdbid) {

        $body_content = json_decode($request->getContent());
        if (empty($body_content->contactPoint) || !isset($body_content->contactPoint->url) || !isset($body_content->contactPoint->email) || !isset($body_content->contactPoint->phone)) {
            return new JsonResponse(['error' => "contactPoint and his properties required"], 400);
        }

        $response = new JsonResponse();
        try {
            $command_id = $this->editor->updateContactPoint($cdbid, new ContactPoint($body_content->contactPoint->phone, $body_content->contactPoint->email, $body_content->contactPoint->url));
            $response->setData(['commandId' => $command_id]);
        }
        catch (Exception $e) {
            $response->setStatusCode(400);
            $response->setData(['error' => $e->getMessage()]);
            watchdog_exception('udb3', $e);
        }

        return $response;

    }

    /**
     * Update the bookingInfo.
     *
     * @param Request $request
     * @param string $cdbid
     * @return JsonResponse
     */
    public function updateBookingInfo(Request $request, $cdbid) {

        $body_content = json_decode($request->getContent());
        if (empty($body_content->bookingInfo)) {
            return new JsonResponse(['error' => "bookingInfo required"], 400);
        }

        $response = new JsonResponse();
        try {
            $data = $body_content->bookingInfo;
            $bookingInfo = new BookingInfo($data->url, $data->urlLabel, $data->phone, $data->email,
                $data->availabilityStarts, $data->availabilityEnds, $data->availabilityStarts, $data->availabilityStarts);
            $command_id = $this->editor->updateBookingInfo($cdbid, $bookingInfo);
            $response->setData(['commandId' => $command_id]);
        }
        catch (Exception $e) {
            $response->setStatusCode(400);
            $response->setData(['error' => $e->getMessage()]);
            watchdog_exception('udb3', $e);
        }

        return $response;

    }

    /**
     * Update the facilities.
     *
     * @param Request $request
     * @param string $cdbid
     * @return JsonResponse
     */
    public function updateFacilities(Request $request, $cdbid) {

        $body_content = json_decode($request->getContent());
        if (empty($body_content->facilities)) {
            return new JsonResponse(['error' => "facilities required"], 400);
        }

        $response = new JsonResponse();
        try {
            $command_id = $this->editor->updateFacilities($cdbid, $body_content->facilities);
            $response->setData(['commandId' => $command_id]);
        }
        catch (Exception $e) {
            $response->setStatusCode(400);
            $response->setData(['error' => $e->getMessage()]);
            watchdog_exception('udb3', $e);
        }

        return $response;

    }

    /**
     * Add an image.
     *
     * @param Request $request
     * @param type $cdbid
     */
    public function addImage(Request $request, $cdbid) {

        if (!$request->files->has('file')) {
            return new JsonResponse(['error' => "file required"], 400);
        }

        // Save the image in drupal files.
        $drupal_file = $this->saveUploadedImage($request->files->get('file'), $cdbid, $this->getImageDestination($cdbid));
        if (!$drupal_file) {
            return new JsonResponse(['error' => "Error while saving file"], 400);
        }

        $description = $request->request->get('description');
        $copyrightHolder = $request->request->get('copyrightHolder');

        // Create the command and return the url to the image + thumbnail version.
        $response = new JsonResponse();
        try {
            $url = file_create_url($drupal_file->getFileUri());
            $thumbnail_url = ImageStyle::load('thumbnail')->buildUrl($drupal_file->getFileUri());
            $command_id = $this->editor->addImage($cdbid, new MediaObject($url, $thumbnail_url, $description, $copyrightHolder, '', 'ImageObject'));
            $response->setData(['commandId' => $command_id, 'thumbnailUrl' => $thumbnail_url, 'url' => $url]);
        }
        catch (Exception $e) {
            $response->setStatusCode(400);
            $response->setData(['error' => $e->getMessage()]);
            watchdog_exception('udb3', $e);
        }

        return $response;

    }

    /**
     * Update an image.
     *
     * @param Request $request
     * @param string $cdbid
     * @param string $index
     */
    public function updateImage(Request $request, $cdbid, $index) {

        $response = new JsonResponse();
        try {

            $itemJson = $this->getItem($cdbid);
            $item = json_decode($itemJson);
            if (!isset($item->mediaObject[$index])) {
                return new JsonResponse(['error' => "The image to edit was not found"], 400);
            }

            // Get the fid of the old file.
            $url = $item->mediaObject[$index]->url;
            $thumbnail_url = $item->mediaObject[$index]->thumbnailUrl;
            $old_fid = $this->getFileIdByUrl($url);

            // A new file was uploaded.
            if ($request->files->has('file')) {

                $drupal_file = $this->saveUploadedImage($request->files->get('file'), $cdbid, $this->getImageDestination($cdbid));
                if (!$drupal_file) {
                    return new JsonResponse(['error' => "Error while saving file"], 400);
                }

                $url = file_create_url($drupal_file->getFileUri());
                $thumbnail_url = ImageStyle::load('thumbnail')->buildUrl($drupal_file->getFileUri());

                $description = $request->request->get('description');
                $copyright = $request->request->get('copyrightHolder');

            }
            // Use existing url.
            else {

                // Format is json if only the text was changed.
                $body_content = json_decode($request->getContent());
                $description = empty($body_content->description) ? '' : $body_content->description;
                $copyright = empty($body_content->copyrightHolder) ? '' : $body_content->copyrightHolder;

            }

            $command_id = $this->editor->updateImage($cdbid, $index, new MediaObject($url, $thumbnail_url, $description, $copyright, $old_fid, 'ImageObject'));
            $response->setData(['commandId' => $command_id, 'thumbnailUrl' => $thumbnail_url, 'url' => $url]);
        }
        catch (Exception $e) {
            $response->setStatusCode(400);
            $response->setData(['error' => $e->getMessage()]);
            watchdog_exception('udb3', $e);
        }

        return $response;

    }

    /**
     * Delete an image.
     *
     * @param Request $request
     * @param string $cdbid
     * @param string $index
     */
    public function deleteImage($cdbid, $index) {

        try {

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
        }
        catch (Exception $e) {
            $response->setStatusCode(400);
            $response->setData(['error' => $e->getMessage()]);
        }

        return $response;

    }

    /**
     * Init the calendar object to use for a create (event / place)
     */
    protected function initCalendarForCreate($body_content) {

        // Cleanup empty timestamps.
        $timestamps = array();
        if (!empty($body_content->timestamps)) {
            foreach ($body_content->timestamps as $timestamp) {
                if (!empty($timestamp->date)) {
                    $date = date('Y-m-d', strtotime($timestamp->date));

                    // Check if a correct starthour is given.
                    if (!empty($timestamp->showStartHour) && !empty($timestamp->startHour)) {

                        list($hour, $minute) = explode(':', $timestamp->startHour);
                        if (strlen($hour) == 2 && strlen($minute) == 2) {
                            $startDate = $date . 'T' . $timestamp->startHour . ':00';
                        }
                        else {
                            $startDate = $date . 'T00:00:00';
                        }

                    }
                    else {
                        $startDate = $date . 'T00:00:00';
                    }

                    // Check if a correct endhour is given.
                    if (!empty($timestamp->showEndHour) && !empty($timestamp->endHour)) {

                        list($hour, $minute) = explode(':', $timestamp->endHour);
                        if (strlen($hour) == 2 && strlen($minute) == 2) {
                            $endDate = $date . 'T' . $timestamp->endHour . ':00';
                        }
                        else {
                            $endDate = $date . 'T00:00:00';
                        }

                    }
                    else {
                        $endDate = $date . 'T00:00:00';
                    }

                    $timestamps[strtotime($startDate)] = new Timestamp($startDate, $endDate);
                }
            }
            ksort($timestamps);
        }

        $startDate = !empty($body_content->startDate) ? $body_content->startDate : '';
        $endDate = !empty($body_content->endDate) ? $body_content->endDate : '';

        // For single calendar type, check if it should be multiple
        // Also calculate the correct startDate and endDate for the calendar object.
        $calendarType = !empty($body_content->calendarType) ? $body_content->calendarType : 'permanent';
        if ($calendarType == Calendar::SINGLE && count($timestamps) == 1) {

            // 1 timestamp = no timestamps needed. Copy start and enddate.
            $firstTimestamp = current($timestamps);
            $startDate = $firstTimestamp->getStartDate();
            $endDate = $firstTimestamp->getEndDate();
            $timestamps = array();
        }
        elseif ($calendarType == Calendar::SINGLE && count($timestamps) > 1) {

            // Multiple timestamps, startDate = first date, endDate = last date.
            $calendarType = Calendar::MULTIPLE;
            $firstTimestamp = current($timestamps);
            $lastTimestamp = end($timestamps);
            $startDate = $firstTimestamp->getStartDate();
            $endDate = $lastTimestamp->getEndDate();

        }

        // Remove empty opening hours.
        $openingHours = array();
        if (!empty($body_content->openingHours)) {
            $openingHours = $body_content->openingHours;
            foreach ($openingHours as $key => $openingHour) {
                if (empty($openingHour->dayOfWeek) || empty($openingHour->opens) || empty($openingHour->closes)) {
                    unset($openingHours[$key]);
                }
            }
        }

        return new Calendar($calendarType, $startDate, $endDate, $timestamps, $openingHours);
    }

    /**
     * Save the uploaded image to the destination folder.
     */
    protected function saveUploadedImage(UploadedFile $file, $itemId, $destination) {

        $filename = $file->getClientOriginalName();

        // Save the image in drupal files.
        file_prepare_directory($destination, FILE_CREATE_DIRECTORY);

        $file = file_save_data(file_get_contents($file->getPathname()), $destination . '/' . $filename, FILE_EXISTS_RENAME);
        $this->fileUsage->add($file, 'culturefeed_udb3', 'udb3_item', $itemId);

        return $file;

    }

    /**
     * Get the file id of a given url.
     */
    protected function getFileIdByUrl($url) {

        $public_files_path = Settings::get('file_public_path', conf_path() . '/files');
        $uri = str_replace($GLOBALS['base_url'] . '/' . $public_files_path . '/', 'public://', $url);

        return db_query('SELECT fid FROM {file_managed} WHERE uri = :uri', array(':uri' => $uri))->fetchField();

    }

}
