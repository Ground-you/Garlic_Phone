<template>
    <Head title="홈 - Garlic Phone" />

    <div class="min-h-screen bg-gradient-to-br from-[#caaae6] via-[#bfa2db] to-[#e4d4f7] bg-[length:400%_400%] animate-gradient-slow flex items-center justify-center p-6 relative overflow-hidden select-none">
        
        <div class="absolute inset-0 opacity-30 animate-move-bg" style="background-image: radial-gradient(#865bc6 2.4px, transparent 1.8px); background-size: 28px 28px;"></div>

        <div class="absolute w-72 h-72 bg-white/10 rounded-full blur-2xl top-[-10%] left-[-5%] animate-float-slow"></div>
        <div class="absolute w-96 h-96 bg-pink-300/10 rounded-full blur-3xl bottom-[-10%] right-[-5%] animate-float-delayed"></div>
        <div class="absolute w-48 h-48 bg-purple-400/10 rounded-full blur-xl top-[60%] left-[80%] animate-float-slow"></div>

        <div class="w-full max-w-7xl min-h-[650px] bg-[#dfcef2]/95 backdrop-blur-sm border-[4px] border-[#e953a8] rounded-3xl p-10 shadow-2xl relative z-10 flex flex-col md:flex-row gap-12 justify-between items-stretch transition-all duration-500">
            
            <div class="w-full md:w-[45%] flex flex-col justify-between">
                <div class="flex justify-start -mt-10 -ml-10 mb-12">
                    <img src="/images/logo.png" alt="Garlic Phone Logo" class="w-44 h-44 object-contain drop-shadow-md hover:scale-105 transition-transform" />
                </div>

                <div class="relative flex items-center w-full pl-6">
                    <div class="w-full bg-[#bfa2db] border-[4px] border-[#865bc6] rounded-2xl p-1.5 shadow-sm">
                        <input 
                            v-model="nickname"
                            type="text" 
                            class="w-full bg-transparent border-none text-white font-black text-center text-xl focus:outline-none placeholder-purple-200 pl-20 pr-4 py-2"
                            placeholder="닉네임 입력..."
                            maxLength="12"
                        />
                    </div>
                    
                    <div class="absolute left-0 w-24 h-24 rounded-full border-[4px] border-[#865bc6] bg-white overflow-hidden shadow-md flex items-center justify-center z-10 group cursor-pointer">
                        <img src="/images/profile.png" alt="User Profile" class="w-full h-full object-cover group-hover:scale-110 transition-transform" />
                    </div>
                </div>

                <div class="flex-1 bg-[#bfa2db] border-[4px] border-[#865bc6] rounded-3xl relative mt-10 pt-12 p-6 shadow-inner flex flex-col justify-center">
                    <div class="absolute -top-4 left-6 bg-[#865bc6] text-white font-extrabold px-5 py-1.5 rounded-xl text-sm shadow-md">
                        플레이 방법
                    </div>
                    <div class="text-[#4a287e] font-bold text-lg space-y-4 leading-relaxed">
                        <p>1. 닉네임을 입력한 후 게임 모드를 선택하세요.</p>
                        <p>2. [방 생성]을 눌러 대기방을 개설합니다.</p>
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
                            class="border-[4px] px-8 py-2.5 rounded-t-2xl font-black text-base transition-all relative top-[4px] z-30 transform active:scale-95"
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
                            class="border-[4px] px-8 py-2.5 rounded-t-2xl font-black text-base transition-all relative top-[4px] z-30 transform active:scale-95"
                        >
                            설정
                        </button>
                    </div>

                    <div class="custom-scroll bg-[#bfa2db] border-[4px] border-[#865bc6] rounded-3xl p-6 shadow-inner w-full h-[520px] min-h-[520px] overflow-y-auto relative z-10 flex flex-col">
                        
                        <Transition name="fade" mode="out-in">
                            <div v-if="activeTab === 'mode'" key="mode" class="space-y-4 w-full flex flex-col">
                                <div 
                                    v-for="mode in modes" 
                                    :key="mode.id"
                                    @click="mode.active ? selectedMode = mode.id : null"
                                    :class="[
                                        !mode.active ? 'opacity-50 cursor-not-allowed bg-gray-200/50 text-gray-400' : '',
                                        selectedMode === mode.id && mode.active ? 'border-[#e953a8] ring-4 ring-pink-200 bg-white scale-[1.01]' : 'border-transparent bg-white hover:bg-purple-50 hover:scale-[1.005]'
                                    ]"
                                    class="w-full border-4 rounded-2xl p-6 flex items-center justify-between shadow-sm cursor-pointer transition-all flex-shrink-0"
                                >
                                    <div class="flex items-center gap-5">
                                        <div class="w-20 h-20 rounded-full border-2 border-purple-300 flex items-center justify-center bg-purple-50 overflow-hidden">
                                            <img :src="mode.image" alt="Mode Icon" class="w-full h-full object-cover" />
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-black text-2xl text-gray-700">{{ mode.title }}</span>
                                        </div>
                                    </div>
                                    <div v-if="selectedMode === mode.id && mode.active" class="w-6 h-6 bg-[#e953a8] rounded-full animate-ping-once"></div>
                                </div>
                            </div>

                            <div v-else key="setting" class="flex-1 w-full flex items-center justify-center text-center text-white font-black text-2xl px-4">
                                사운드 및 시스템 설정이 추가될 예정입니다.
                            </div>
                        </Transition>
                        
                    </div>
                </div>

                <button 
                    @click="handleStartGame"
                    :disabled="!isNicknameValid"
                    :class="[
                        isNicknameValid 
                            ? 'bg-[#bfa2db] hover:bg-[#b090cf] border-[#865bc6] hover:border-[#734dae] text-[#55328a] cursor-pointer transform active:scale-[0.98]' 
                            : 'bg-purple-300/50 border-purple-400 text-purple-400 opacity-60 cursor-not-allowed'
                    ]"
                    class="w-full mt-6 border-[4px] font-black text-3xl py-5 rounded-2xl shadow-md transition-all duration-200"
                >
                    방 생성
                </button>
            </div>

        </div>
    </div>
</template>

<style scoped>
@keyframes gradientBg {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

@keyframes moveBg {
    0% { background-position: 0px 0px; }
    100% { background-position: 56px 56px; }
}

@keyframes float {
    0% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-25px) rotate(5deg); }
    100% { transform: translateY(0px) rotate(0deg); }
}

@keyframes floatDelayed {
    0% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(30px) rotate(-5deg); }
    100% { transform: translateY(0px) rotate(0deg); }
}

@keyframes bounceSlow {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-6px); }
}

.animate-gradient-slow {
    animation: gradientBg 18s ease infinite;
}
.animate-move-bg {
    animation: moveBg 5s linear infinite;
}
.animate-float-slow {
    animation: float 8s ease-in-out infinite;
}
.animate-float-delayed {
    animation: floatDelayed 10s ease-in-out infinite;
}
.animate-bounce-slow {
    animation: bounceSlow 3s ease-in-out infinite;
}
.animate-ping-once {
    animation: ping 0.5s cubic-bezier(0, 0, 0.2, 1) 1;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}
.fade-enter-from {
    opacity: 0;
    transform: translateY(4px);
}
.fade-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}

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
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    defaultNickname: String,
    modes: Array
});

const nickname = ref(props.defaultNickname || '');
const selectedMode = ref('normal');
const activeTab = ref('mode');

const isNicknameValid = computed(() => {
    return nickname.value && nickname.value.trim().length > 0;
});

const handleStartGame = () => {
    if (!isNicknameValid.value) return;
    alert(`게임 시작! 닉네임: ${nickname.value}, 선택한 모드: ${selectedMode.value}`);
};
</script>