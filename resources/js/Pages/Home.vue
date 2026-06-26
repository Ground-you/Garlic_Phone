<template>
    <Head title="홈 - Garlic Phone" />

    <div class="min-h-screen bg-[#cbb4e4] flex items-center justify-center p-6 relative overflow-hidden select-none">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#000 1px, transparent 1px); background-size: 16px 16px;"></div>

        <div class="w-full max-w-7xl min-h-[650px] bg-[#dfcef2] border-[4px] border-[#e953a8] rounded-3xl p-10 shadow-2xl relative z-10 flex flex-col md:flex-row gap-12 justify-between items-stretch">
            
            <div class="w-full md:w-[45%] flex flex-col justify-between">
                <div class="flex justify-start -mt-10 -ml-10 mb-12">
                    <img src="/images/logo.png" alt="Garlic Phone Logo" class="w-44 h-44 object-contain drop-shadow-md" />
                </div>

                <div class="relative flex items-center w-full pl-6">
                    <div class="w-full bg-[#bfa2db] border-[4px] border-[#865bc6] rounded-2xl p-1.5 shadow-sm">
                        <input 
                            v-model="nickname"
                            type="text" 
                            class="w-full bg-transparent border-none text-white font-black text-center text-xl focus:outline-none placeholder-purple-200 pl-20 pr-4 py-2"
                            placeholder="닉네임 입력..."
                        />
                    </div>
                    
                    <div class="absolute left-0 w-24 h-24 rounded-full border-[4px] border-[#865bc6] bg-white overflow-hidden shadow-md flex items-center justify-center z-10">
                        <img src="/images/profile.png" alt="User Profile" class="w-full h-full object-cover" />
                    </div>
                </div>

                <div class="flex-1 bg-[#bfa2db] border-[4px] border-[#865bc6] rounded-3xl relative mt-10 pt-12 p-6 shadow-inner flex flex-col justify-center">
                    <div class="absolute -top-4 left-6 bg-[#865bc6] text-white font-extrabold px-5 py-1.5 rounded-xl text-sm shadow-md">
                        플레이 방법
                    </div>
                    <div class="text-[#4a287e] font-bold text-lg space-y-4 leading-relaxed">
                        <p>1. 닉네임을 입력한 후 게임 모드를 선택하세요.</p>
                        <p>2. [플레이 시작]을 눌러 방을 생성하거나 대기방에 입장합니다.</p>
                        <p>3. 친구들과 함께 실시간으로 이어 그리고 정답을 맞춰보세요!</p>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-[50%] flex flex-col justify-between pt-6">
                <div class="flex flex-col w-full">
                    <div class="flex gap-1 pl-4 relative z-20">
                        <button 
                            @click="activeTab = 'mode'"
                            :class="[
                                activeTab === 'mode' 
                                    ? 'bg-[#bfa2db] border-[#865bc6] text-white border-b-transparent' 
                                    : 'bg-[#dfcef2] border-[#865bc6] text-[#865bc6] border-b-[#865bc6] hover:bg-purple-100'
                            ]"
                            class="border-[4px] px-8 py-2.5 rounded-t-2xl font-black text-base transition-all relative top-[4px] z-30"
                        >
                            모드
                        </button>

                        <button 
                            @click="activeTab = 'setting'"
                            :class="[
                                activeTab === 'setting' 
                                    ? 'bg-[#bfa2db] border-[#865bc6] text-white border-b-transparent' 
                                    : 'bg-[#dfcef2] border-[#865bc6] text-[#865bc6] border-b-[#865bc6] hover:bg-purple-100'
                            ]"
                            class="border-[4px] px-8 py-2.5 rounded-t-2xl font-black text-base transition-all relative top-[4px] z-30"
                        >
                            설정
                        </button>
                    </div>

                    <div class="custom-scroll bg-[#bfa2db] border-[4px] border-[#865bc6] rounded-3xl p-6 shadow-inner w-full h-[520px] min-h-[520px] overflow-y-auto relative z-10 flex flex-col">
                        
                        <template v-if="activeTab === 'mode'">
                            <div class="space-y-4 w-full flex flex-col">
                                <div 
                                    v-for="mode in modes" 
                                    :key="mode.id"
                                    @click="mode.active ? selectedMode = mode.id : null"
                                    :class="[
                                        !mode.active ? 'opacity-60 cursor-not-allowed bg-gray-100 text-gray-400' : '',
                                        selectedMode === mode.id && mode.active ? 'border-[#e953a8] ring-4 ring-pink-200 bg-white' : 'border-transparent bg-white hover:bg-purple-50'
                                    ]"
                                    class="w-full border-4 rounded-2xl p-6 flex items-center justify-between shadow-sm cursor-pointer transition-all flex-shrink-0"
                                >
                                    <div class="flex items-center gap-5">
                                        <div class="w-16 h-16 rounded-full border-2 border-purple-300 flex items-center justify-center bg-purple-50 text-3xl">
                                            🎨
                                        </div>
                                        <span class="font-black text-2xl text-gray-700">{{ mode.title }}</span>
                                    </div>
                                    <div v-if="selectedMode === mode.id && mode.active" class="w-6 h-6 bg-[#e953a8] rounded-full"></div>
                                </div>
                            </div>
                        </template>

                        <template v-else>
                            <div class="flex-1 w-full flex items-center justify-center text-center text-white font-black text-2xl px-4">
                                사운드 및 시스템 설정이 추가될 예정입니다.
                            </div>
                        </template>
                        
                    </div>
                </div>

                <button 
                    @click="handleStartGame"
                    class="w-full mt-6 bg-[#bfa2db] hover:bg-[#b090cf] border-[4px] border-[#865bc6] hover:border-[#734dae] text-[#55328a] font-black text-3xl py-5 rounded-2xl shadow-md transform active:scale-[0.98] transition-all"
                >
                    방 생성
                </button>
            </div>

        </div>
    </div>
</template>

<style scoped>
.custom-scroll::-webkit-scrollbar {
    width: 8px;
}
.custom-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scroll::-webkit-scrollbar-thumb {
    background-color: #865bc6;
    border-radius: 9999px;
}
input::placeholder {
    color: rgba(255, 255, 255, 0.6);
}
</style>

<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    defaultNickname: String,
    modes: Array
});

const nickname = ref(props.defaultNickname);
const selectedMode = ref('normal');
const activeTab = ref('mode');

const handleStartGame = () => {
    if (!nickname.value || !nickname.value.trim()) {
        alert('닉네임을 입력해 주세요!');
        return;
    }
    alert(`게임 시작! 닉네임: ${nickname.value}, 선택한 모드: ${selectedMode.value}`);
};
</script>