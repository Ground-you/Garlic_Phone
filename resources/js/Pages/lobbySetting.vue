<template>
    <Transition name="modal-fade">
        <div v-if="isOpen" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
            
            <div class="relative w-full max-w-2xl">

                <button 
                    @click="$emit('close')"
                    class="absolute -top-4 -right-4 w-10 h-10 bg-white border-[3px] border-[#865bc6] rounded-full flex items-center justify-center text-[#865bc6] font-black text-xl shadow-md hover:bg-gray-100 active:scale-90 transition-all z-10"
                >
                    ✕
                </button>

                <div class="w-full bg-[#b794cc] border-[4px] border-[#865bc6] rounded-3xl shadow-2xl relative flex flex-col items-center pt-10 pb-6 px-6">
                    
                    <div class="absolute -top-[32px] left-8 bg-[#9c6eb2] border-[4px] border-[#865bc6] border-b-transparent text-white font-black px-12 py-1.5 rounded-t-2xl text-lg">
                        방 설정
                    </div>

                    <div class="w-full h-[380px] overflow-y-auto space-y-4 pr-1 custom-scroll mb-6">
                        
                        <div 
                            class="bg-white border-[3px] border-transparent rounded-2xl p-4 transition-all cursor-pointer overflow-hidden" 
                            :class="{'border-[#4ceea3] ring-4 ring-emerald-100': activeAccordion === 'player'}"
                            @click="toggleAccordion('player')"
                        >
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 bg-[#caaae6] rounded-full flex items-center justify-center text-2xl border-2 border-purple-400 text-white">👥</div>
                                <div class="flex-1">
                                    <h4 class="font-black text-xl text-gray-800">플레이어 수</h4>
                                    <p class="text-sm text-gray-500 font-bold mt-1">기본 시작 인원 수를 설정합니다.</p>
                                </div>
                                <span class="text-gray-400 font-black text-lg transition-transform" :class="{'rotate-180': activeAccordion === 'player'}">⌄</span>
                            </div>

                            <div 
                                v-show="activeAccordion === 'player'"
                                class="mt-4 pt-4 border-t-2 border-gray-100 flex items-center justify-center gap-4 select-none"
                            >
                                <button @click.stop="adjustPlayer(-1)" class="w-10 h-10 rounded-full border-2 border-gray-400 bg-white font-black text-xl flex items-center justify-center text-gray-600 hover:bg-gray-100 active:scale-90 transition-transform">－</button>
                                <span class="font-black text-2xl text-gray-800 w-10 text-center">{{ localSettings.players }}</span>
                                <button @click.stop="adjustPlayer(1)" class="w-10 h-10 rounded-full border-2 border-gray-400 bg-white font-black text-xl flex items-center justify-center text-gray-600 hover:bg-gray-100 active:scale-90 transition-transform">＋</button>
                            </div>
                        </div>

                        <div 
                            class="bg-white border-[3px] border-transparent rounded-2xl p-4 transition-all cursor-pointer overflow-hidden" 
                            :class="{'border-[#4ceea3] ring-4 ring-emerald-100': activeAccordion === 'time'}"
                            @click="toggleAccordion('time')"
                        >
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 bg-[#caaae6] rounded-full flex items-center justify-center text-2xl border-2 border-purple-400 text-white">🕒</div>
                                <div class="flex-1">
                                    <h4 class="font-black text-xl text-gray-800">시간 설정</h4>
                                    <p class="text-sm text-gray-500 font-bold mt-1">다음 턴으로 넘어가기까지의 시간을 설정합니다.</p>
                                </div>
                                <span class="text-gray-400 font-black text-lg transition-transform" :class="{'rotate-180': activeAccordion === 'time'}">⌄</span>
                            </div>

                            <div 
                                v-show="activeAccordion === 'time'"
                                class="mt-4 pt-4 border-t-2 border-gray-100 flex items-center justify-center gap-4 select-none"
                            >
                                <button @click.stop="adjustTime(-5)" class="w-10 h-10 rounded-full border-2 border-gray-400 bg-white font-black text-xl flex items-center justify-center text-gray-600 hover:bg-gray-100 active:scale-90 transition-transform">－</button>
                                <span class="font-black text-2xl text-gray-800 w-16 text-center">{{ localSettings.timeLimit }}초</span>
                                <button @click.stop="adjustTime(5)" class="w-10 h-10 rounded-full border-2 border-gray-400 bg-white font-black text-xl flex items-center justify-center text-gray-600 hover:bg-gray-100 active:scale-90 transition-transform">＋</button>
                            </div>
                        </div>

                        <div class="bg-white/80 border-[3px] border-transparent rounded-2xl p-4 opacity-75">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 bg-gray-300 rounded-full flex items-center justify-center text-2xl text-white">🔒</div>
                                <div class="flex-1">
                                    <h4 class="font-black text-xl text-gray-400">추후에 추가될 예정</h4>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="w-full flex gap-4">
                        <button 
                            @click="$emit('confirm', localSettings)"
                            class="flex-1 bg-[#caaae6] hover:bg-[#bd9ad9] border-[4px] border-[#865bc6] text-white font-black text-2xl py-3.5 rounded-2xl shadow-md transform active:scale-95 transition-all"
                        >
                            확인
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref } from 'vue';

defineProps({
    isOpen: Boolean
});

defineEmits(['close', 'confirm']);

const activeAccordion = ref('player');

const localSettings = ref({
    players: 2,
    timeLimit: 40
});

const toggleAccordion = (key) => {
    activeAccordion.value = activeAccordion.value === key ? null : key;
};

const adjustPlayer = (amount) => {
    const nextValue = localSettings.value.players + amount;
    if (nextValue >= 2 && nextValue <= 12) {
        localSettings.value.players = nextValue;
    }
};

const adjustTime = (amount) => {
    const nextValue = localSettings.value.timeLimit + amount;
    if (nextValue >= 10 && nextValue <= 120) {
        localSettings.value.timeLimit = nextValue;
    }
};
</script>

<style scoped>
.modal-fade-enter-active, 
.modal-fade-leave-active { 
    transition: opacity 0.2s ease-out; 
}
.modal-fade-enter-from, 
.modal-fade-leave-to { 
    opacity: 0; 
}

.modal-fade-enter-active > div,
.modal-fade-leave-active > div {
    transition: transform 0.2s ease-out, opacity 0.2s ease-out;
}

.modal-fade-enter-from > div {
    transform: scale(0.97);
    opacity: 0;
}
.modal-fade-leave-to > div {
    transform: scale(0.98);
    opacity: 0;
}

.custom-scroll::-webkit-scrollbar { width: 8px; }
.custom-scroll::-webkit-scrollbar-track { background: transparent; }
.custom-scroll::-webkit-scrollbar-thumb { background-color: #865bc6; border-radius: 9999px; }
</style>