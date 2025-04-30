<?php
/**
 * Клас для управління інтерфейсом адміністратора (структура меню і вкладок).
 *
 * @package EmboSettings
 */

namespace EmboSettings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Admin_Interface {

    /**
     * Об'єкт модулю з кольоровими налаштуваннями.
     *
     * @var \EmboSettings\Colors_Tab
     */
    private $colors_tab;

    /**
     * Конструктор класу.
     *
     * @param \EmboSettings\Colors_Tab $colors_tab Об'єкт класу Colors_Tab.
     */
    public function __construct( $colors_tab ) {
        $this->colors_tab = $colors_tab;
    }
}