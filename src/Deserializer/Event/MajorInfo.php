<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\Event;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Location\Location;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;

class MajorInfo
{
    /**
     * @var Language
     */
    private $mainLanguage;

    /**
     * @var Title
     */
    private $title;

    /**
     * @var EventType
     */
    private $type;

    /**
     * @var Location
     */
    private $location;

    /**
     * @var Calendar
     */
    private $calendar;

    /**
     * @var Theme|null
     */
    private $theme;

    /**
     * @param Language $mainLanguage
     * @param Title $title
     * @param EventType $type
     * @param Location $location
     * @param Calendar $calendar
     * @param Theme|null $theme
     */
    public function __construct(
        Language $mainLanguage,
        Title $title,
        EventType $type,
        Location $location,
        Calendar $calendar,
        Theme $theme = null
    ) {
        $this->mainLanguage = $mainLanguage;
        $this->title = $title;
        $this->type = $type;
        $this->location = $location;
        $this->calendar = $calendar;
        $this->theme = $theme;
    }

    /**
     * @return Language
     */
    public function getMainLanguage()
    {
        return $this->mainLanguage;
    }

    /**
     * @return Title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return EventType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return Calendar
     */
    public function getCalendar()
    {
        return $this->calendar;
    }

    /**
     * @return Theme|null
     */
    public function getTheme()
    {
        return $this->theme;
    }
}
