import { usePage } from '@inertiajs/vue3';

export function usePermissions() {
    const page = usePage();

    function can(permission: string): boolean {
        const permissions: string[] = (page.props.auth?.user as any)?.permissions || [];
        if (permissions.includes('*')) {
            return true;
        }
        return permissions.includes(permission);
    }

    function canAny(perms: string[]): boolean {
        return perms.some(p => can(p));
    }

    function canAll(perms: string[]): boolean {
        return perms.every(p => can(p));
    }

    return {
        can,
        canAny,
        canAll,
    };
}
