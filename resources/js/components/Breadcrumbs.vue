<script setup lang="ts">
/**
 * Componente: Breadcrumbs
 * Propósito: Miga de pan de navegación jerárquica institucional.
 *
 * Actualización (Optimización de Nombres Largos y Truncado Reactivo):
 * Se añadieron restricciones de ancho máximo y truncado (`truncate max-w-...`)
 * junto al atributo `title` nativo para evitar desbordamientos horizontales
 * cuando los módulos o registros posean denominaciones institucionales extensas.
 */
import { Link } from '@inertiajs/vue3';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

type Props = {
    breadcrumbs: BreadcrumbItemType[];
};

defineProps<Props>();
</script>

<template>
    <Breadcrumb>
        <BreadcrumbList>
            <template v-for="(item, index) in breadcrumbs" :key="index">
                <BreadcrumbItem>
                    <template v-if="index === breadcrumbs.length - 1">
                        <div class="flex flex-col min-w-0">
                            <BreadcrumbPage class="text-base font-medium truncate max-w-[240px] sm:max-w-xs md:max-w-md" :title="item.title">
                                {{ item.title }}
                            </BreadcrumbPage>
                            <BreadcrumbPage v-if="item.subtitle" class="text-xs text-foreground-muted truncate max-w-[240px] sm:max-w-xs md:max-w-md" :title="item.subtitle">
                                {{ item.subtitle }}
                            </BreadcrumbPage>
                        </div>
                    </template>
                    <template v-else>
                        <BreadcrumbLink as-child class="truncate max-w-[140px] sm:max-w-[180px]" :title="item.title">
                            <Link :href="item.href">{{ item.title }}</Link>
                        </BreadcrumbLink>
                    </template>
                </BreadcrumbItem>
                <BreadcrumbSeparator v-if="index !== breadcrumbs.length - 1" />
            </template>
        </BreadcrumbList>
    </Breadcrumb>
</template>
