<script setup lang="ts">
import { type Component } from 'vue';
import { Link } from '@inertiajs/vue3';

interface Props {
  title: string;
  value: string | number;
  icon: Component;
  trend?: string;
  trendValue?: string;
  trendColor?: 'primary' | 'destructive' | 'muted';
  href?: string;
}

withDefaults(defineProps<Props>(), {
  trendColor: 'primary',
  href: '#',
});
</script>

<template>
  <Link :href="href" class="block group">
    <div 
      data-slot="card" 
      class="bg-card text-card-foreground rounded-xl border border-border group-hover:border-primary/50 shadow-sm transition-colors duration-200"
    >
      <div data-slot="card-header" class="gap-1.5 p-6 flex flex-row items-center justify-between pb-2">
        <div data-slot="card-title" class="tracking-tight text-sm font-medium text-foreground/80">
          {{ title }}
        </div>
        <component :is="icon" class="h-4 w-4 text-primary" aria-hidden="true" />
      </div>

      <div data-slot="card-content" class="p-6 pt-0">
        <div class="text-2xl font-bold tabular-nums">{{ value }}</div>
        
        <p v-if="trendValue" class="text-xs text-muted-foreground mt-1 flex items-center gap-1">
          <span 
            :class="[
              'font-semibold',
              trendColor === 'primary' ? 'text-primary' : '',
              trendColor === 'destructive' ? 'text-destructive' : '',
              trendColor === 'muted' ? 'text-muted-foreground' : ''
            ]"
          >
            {{ trendValue }}
          </span>
          {{ trend }}
        </p>
      </div>
    </div>
  </Link>
</template>