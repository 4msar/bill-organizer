import { Page } from '@inertiajs/core';
import { RouteParams, Router } from 'ziggy-js';
import { SharedData } from '.';

declare global {
    function route(): Router;
    function route(name: string, params?: RouteParams<typeof name> | undefined, absolute?: boolean): string;
}

declare module '@vue/runtime-core' {
    interface ComponentCustomProperties {
        route: typeof route;
        $page: Page<SharedData>;
    }
}
