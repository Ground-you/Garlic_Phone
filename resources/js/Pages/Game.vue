<template>
    <Head title="게임 - Garlic Phone" />
    <div class="min-h-screen bg-[#bfa2db] flex items-center justify-center p-4">
        <h1 class="text-white font-black text-2xl">인게임 화면 (방 코드: {{ lobbyCode }})</h1>
    </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
defineProps({ lobbyCode: String });
</script>