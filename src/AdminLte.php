<?php

namespace JeroenNoten\LaravelAdminLte;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\View;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use JeroenNoten\LaravelAdminLte\Menu\Builder;

class AdminLte
{
    protected $menu;

    protected $filters;

    protected $events;

    protected $container;

    public function __construct(
        array $filters,
        Dispatcher $events,
        Container $container
    ) {
        $this->filters = $filters;
        $this->events = $events;
        $this->container = $container;
    }

    public function menu($filterOpt = null)
    {
        if (! $this->menu) {
            $this->menu = $this->buildMenu();
        }

        // Check for filter option.

        if ($filterOpt !== null) {
            $filterOpt = explode('-', $filterOpt);
            $filterOpt = array_map('ucfirst', $filterOpt);
            $filterOpt = lcfirst(implode('', $filterOpt));
            return array_filter($this->menu, [$this, $filterOpt.'Filter']);
        }

        return $this->menu;
    }

    /**
     * Gets the body classes, in relation to the config options.
     */
    public function getBodyClasses()
    {
        $body_classes = [];

        // Add classes related to the "sidebar_mini" configuration.

        if (config('adminlte.sidebar_mini', true) === true) {
            $body_classes[] = 'sidebar-mini';
        } elseif (config('adminlte.sidebar_mini', true) == 'md') {
            $body_classes[] = 'sidebar-mini sidebar-mini-md';
        }

        // Add classes related to the "layout_topnav" configuration.

        if (config('adminlte.layout_topnav') || View::getSection('layout_topnav')) {
            $body_classes[] = 'layout-top-nav';
        }

        // Add classes related to the "layout_boxed" configuration.

        if (config('adminlte.layout_boxed') || View::getSection('layout_boxed')) {
            $body_classes[] = 'layout-boxed';
        }

        // Add classes related to the "sidebar_collapse" configuration.

        if (config('adminlte.sidebar_collapse') || View::getSection('sidebar_collapse')) {
            $body_classes[] = 'sidebar-collapse';
        }

        // Add classes related to the "right_sidebar" configuration.

        if (config('adminlte.right_sidebar') && config('adminlte.right_sidebar_push')) {
            $body_classes[] = 'control-sidebar-push';
        }

        // Add classes related to fixed sidebar, these are not compatible with
        // "layout_topnav" and check for fixed sidebar configuration.

        if ((!config('adminlte.layout_topnav') && !View::getSection('layout_topnav')) && config('adminlte.layout_fixed_sidebar')) {
            $body_classes[] = 'layout-fixed';
        }

        // Add classes related to fixed footer and navbar, these are not
        // compatible with "layout_boxed".

        if (! config('adminlte.layout_boxed') && ! View::getSection('layout_boxed')) {

            // Check for fixed navbar configuration.

            $body_classes = array_merge($body_classes, $this->fixedConfigCheck('navbar'));

            // Check for fixed footer configuration.
            $body_classes = array_merge($body_classes, $this->fixedConfigCheck('footer'));
        }

        // Add custom classes, related to the "classes_body" configuration.

        $body_classes[] = config('adminlte.classes_body', '');

        // Return the set of configured classes for the body tag.

        return trim(implode(' ', $body_classes));
    }

    /**
     * Gets the body data attributes, in relation to the config options.
     */
    public function getBodyData()
    {
        $body_data = [];

        // Add data related to the "sidebar_scrollbar_theme" configuration.

        $sb_theme_cfg = config('adminlte.sidebar_scrollbar_theme', 'os-theme-light');

        if ($sb_theme_cfg != 'os-theme-light') {
            $body_data[] = 'data-scrollbar-theme='.$sb_theme_cfg;
        }

        // Add data related to the "sidebar_scrollbar_auto_hide" configuration.

        $sb_auto_hide = config('adminlte.sidebar_scrollbar_auto_hide', 'l');

        if ($sb_auto_hide != 'l') {
            $body_data[] = 'data-scrollbar-auto-hide='.$sb_auto_hide;
        }

        return trim(implode(' ', $body_data));
    }

    protected function buildMenu()
    {
        $builder = new Builder($this->buildFilters());

        $this->events->dispatch(new BuildingMenu($builder));

        return $builder->menu;
    }

    protected function buildFilters()
    {
        return array_map([$this->container, 'make'], $this->filters);
    }

    /**
     * Filter method for sidebar menu items.
     */
    private function sidebarFilter($item)
    {
        if ($this->itemCheck($item) || $this->itemCheck($item, 'right') || $this->itemCheck($item, 'user')) {
            return false;
        }

        return true;
    }

    /**
     * Filter method for navbar top left menu items.
     */
    private function navbarLeftFilter($item)
    {
        if ($this->itemCheck($item, 'right') || $this->itemCheck($item, 'user')) {
            return false;
        }

        if (config('adminlte.layout_topnav') || (isset($item['topnav']) && $item['topnav'])) {
            return is_array($item) && ! isset($item['header']);
        }

        return false;
    }

    /**
     * Filter method for navbar top right menu items.
     */
    private function navbarRightFilter($item)
    {
        return $this->itemCheck($item, 'right');
    }

    /**
     * Filter method for navbar dropdown user menu items.
     */
    private function navbarUserFilter($item)
    {
        return $this->itemCheck($item, 'user');
    }

    /**
     * Item topnav check
     */
    private function itemCheck($item, $type = '') {
        if (! empty($type)) {
            $type = '_'.$type;
        }
        if (isset($item['topnav'.$type]) && $item['topnav'.$type]) {
            return true;
        }

        return false;
    }

    /**
     * Config check for fixed footer & fixed navbar.
     */
    private function fixedConfigCheck($place) {
        $body_classes = [];
        $screen_sizes = ['xs', 'sm', 'md', 'lg', 'xl'];

        $fixed_cfg = config('adminlte.layout_fixed_'.$place);

        if ($fixed_cfg === true) {
            $body_classes[] = 'layout-'.$place.'-fixed';
        } elseif (is_array($fixed_cfg)) {
            foreach ($fixed_cfg as $size => $enabled) {
                if (in_array($size, $screen_sizes)) {
                    $size = $size == 'xs' ? '' : '-'.$size;
                    $body_classes[] = $enabled == true ?
                        'layout'.$size.'-'.$place.'-fixed' :
                        'layout'.$size.'-'.$place.'-not-fixed';
                }
            }
        }

        return $body_classes;
    }
}
