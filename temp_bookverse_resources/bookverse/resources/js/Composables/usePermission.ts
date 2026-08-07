import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Composable for checking the authenticated user's permissions on the frontend.
 *
 * Usage:
 *   const { can, canAny, canAll } = usePermission();
 *   v-if="can('edit-roles')"
 *   v-if="canAny(['edit-roles', 'delete-roles'])"
 */
export function usePermission() {
    const page = usePage();

    const userPermissions = computed<string[]>(() => {
        return (page.props.auth?.user as any)?.permissions ?? [];
    });

    const isSuperAdmin = computed(() => userPermissions.value.includes('*'));

    /**
     * Check if the user has a specific permission.
     */
    function can(permission: string): boolean {
        return userPermissions.value.includes(permission);
    }

    function canAny(permissions: string[]): boolean {
        return permissions.some(p => userPermissions.value.includes(p));
    }

    function canAll(permissions: string[]): boolean {
        return permissions.every(p => userPermissions.value.includes(p));
    }

    /**
     * Check if the user does NOT have a permission.
     */
    function cannot(permission: string): boolean {
        return !can(permission);
    }

    return { can, canAny, canAll, cannot, isSuperAdmin };
}
