<?php

namespace App;

use Carbon\CarbonInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * A one-event calendar file (.ics) that phones and Google Calendar can open.
 */
class CalendarFile
{
    /**
     * Build the download for one event. All-day events carry only their date.
     */
    public static function download(
        string $uid,
        string $title,
        CarbonInterface $start,
        bool $allDay,
        string $location,
        string $description,
        string $url,
    ): Response {
        $utc = fn (CarbonInterface $date): string => $date->copy()->utc()->format('Ymd\THis\Z');

        $timing = $allDay
            ? ['DTSTART;VALUE=DATE:'.$start->format('Ymd'), 'DTEND;VALUE=DATE:'.$start->copy()->addDay()->format('Ymd')]
            : ['DTSTART:'.$utc($start), 'DTEND:'.$utc($start->copy()->addHours(3))];

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//SK Digital Governance//Olongapo City//EN',
            'BEGIN:VEVENT',
            'UID:'.$uid.'@'.parse_url(config('app.url'), PHP_URL_HOST),
            'DTSTAMP:'.$utc(now()),
            ...$timing,
            'SUMMARY:'.self::escape($title),
            'LOCATION:'.self::escape($location),
            'DESCRIPTION:'.self::escape($description),
            'URL:'.$url,
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return response(implode("\r\n", $lines)."\r\n", 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.Str::slug($title).'.ics"',
        ]);
    }

    /**
     * RFC 5545 text: escape \ ; , then turn every kind of line break into "\n" and drop other
     * control characters, so officer-entered text can never start a new line in the file.
     */
    private static function escape(string $text): string
    {
        return preg_replace(
            '/[\x00-\x08\x0B-\x1F\x7F]/',
            '',
            preg_replace('/\r\n|\r|\n/', '\\n', str_replace(['\\', ';', ','], ['\\\\', '\;', '\,'], $text)),
        );
    }
}
