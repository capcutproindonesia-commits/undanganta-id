<?php

namespace App\Support;

final class StudioBindingSchema
{
    public const GALLERY_CAPACITY = 30;

    public static function galleryCapacity(): int
    {
        return self::GALLERY_CAPACITY;
    }

    /**
     * @return array<int, string>
     */
    public static function galleryKeys(): array
    {
        return array_map(
            static fn (int $index): string => 'gallery_' . $index,
            range(1, self::GALLERY_CAPACITY)
        );
    }

    /**
     * Central text binding schema for Admin Studio + Premium/Royal customer content.
     *
     * @return array<string, string>
     */
    public static function textBindings(): array
    {
        return [
            'groom_name' => 'Nama mempelai pria',
            'bride_name' => 'Nama mempelai wanita',
            'couple_names' => 'Nama pasangan',
            'event_date' => 'Tanggal acara',
            'venue_name' => 'Nama lokasi',
            'venue_address' => 'Alamat lokasi',
            'maps_url' => 'Google Maps URL',
            'quote' => 'Quote',
            'prayer' => 'Doa',
            'opening_text' => 'Kalimat pembuka',
            'closing_text' => 'Kalimat penutup',
            'story' => 'Love story',
            'music_url' => 'Music URL',
            'gift_bank' => 'Bank / e-wallet',
            'gift_number' => 'Nomor rekening',
            'gift_name' => 'Nama pemilik rekening',
        ];
    }

    /**
     * Direct image bindings. Gallery aliases remain generated separately.
     *
     * @return array<string, string>
     */
    public static function mediaBindings(): array
    {
        return [
            'groom_photo' => 'Foto mempelai pria',
            'bride_photo' => 'Foto mempelai wanita',
            'couple_photo' => 'Foto pasangan',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function textKeys(): array
    {
        return array_keys(self::textBindings());
    }

    /**
     * @return array<int, string>
     */
    public static function directMediaKeys(): array
    {
        return array_keys(self::mediaBindings());
    }

    /**
     * @return array<int, array{0:string,1:string}>
     */
    public static function textBindingOptions(): array
    {
        $options = [['none', 'Tidak terhubung']];

        foreach (self::textBindings() as $key => $label) {
            $options[] = [$key, $label];
        }

        return $options;
    }

    /**
     * @return array<int, array{0:string,1:string}>
     */
    public static function directMediaBindingOptions(): array
    {
        $options = [['none', 'Tidak terhubung']];

        foreach (self::mediaBindings() as $key => $label) {
            $options[] = [$key, $label];
        }

        return $options;
    }
}
