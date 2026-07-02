<template>
    <Head title="홈 - Garlic Phone" />

    <div class="min-h-screen bg-gradient-to-br from-[#caaae6] via-[#bfa2db] to-[#e4d4f7] flex items-center justify-center p-6 relative overflow-hidden select-none">
        
        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(#865bc6 2.4px, transparent 1.8px); background-size: 28px 28px;"></div>

        <div class="absolute w-72 h-72 bg-white/10 rounded-full blur-md top-[-10%] left-[-5%]"></div>
        <div class="absolute w-96 h-96 bg-pink-300/10 rounded-full blur-md bottom-[-10%] right-[-5%]"></div>

        <div 
            class="w-full max-w-7xl min-h-[650px] bg-[#dfcef2]/95 border-[4px] border-[#e953a8] rounded-3xl p-10 shadow-2xl relative z-10 flex flex-col md:flex-row gap-12 justify-between items-stretch transition-all duration-300 ease-out animate-pop-in"
            :class="{ 'scale-[0.98] opacity-60 pointer-events-none': isModalOpen }"
        >
            
            <div class="w-full md:w-[45%] flex flex-col justify-between">
                <div class="flex justify-start -mt-10 -ml-10 mb-12">
                    <img src="/images/logo.png" alt="Garlic Phone Logo" class="w-44 h-44 object-contain drop-shadow-md hover:scale-105 hover:rotate-1 transition-transform duration-200" />
                </div>

                <div class="relative flex items-center w-full pl-6" v-click-outside="() => isProfileCardOpen = false">
                    <div class="w-full bg-[#bfa2db] border-[4px] border-[#865bc6] rounded-2xl p-1.5 shadow-sm">
                        <input 
                            v-model="nickname"
                            type="text" 
                            class="w-full bg-transparent border-none text-white font-black text-center text-xl focus:outline-none placeholder-purple-200 pl-20 pr-4 py-2"
                            :placeholder="auth.user ? auth.user.name : '닉네임 입력...'"
                            maxLength="12"
                            :disabled="auth.user !== null"
                        />
                    </div>
                    
                    <div 
                        @click="isProfileCardOpen = !isProfileCardOpen"
                        class="absolute left-0 w-24 h-24 rounded-full border-[4px] border-[#865bc6] bg-white overflow-hidden shadow-md flex items-center justify-center z-10 group cursor-pointer active:scale-95 transition-transform duration-150"
                    >
                        <img :src="auth.user ? profilePreview : profilePreview" alt="User Profile" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-200" />
                        <div v-if="!auth.user" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center">
                            <span class="text-white text-1xl">프로필 설정</span>
                        </div>
                    </div>

                    <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="handleProfileChange" />

                    <ProfileCard
                        :isOpen="isProfileCardOpen"
                        :nickname="nickname"
                        :statusMessage="statusMessage"
                        :avatarUrl="profilePreview"
                        :isLoggedIn="!!auth.user"
                        @close="isProfileCardOpen = false"
                        @update:nickname="nickname = $event"
                        @update:statusMessage="statusMessage = $event"
                        @update:avatarUrl="profilePreview = $event"
                    />
                </div>

                <div class="flex-1 bg-[#bfa2db] border-[4px] border-[#865bc6] rounded-3xl relative mt-10 pt-12 p-6 shadow-inner flex flex-col ">
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
                            :class="[activeTab === 'mode' ? 'bg-[#bfa2db] border-[#865bc6] text-white border-b-transparent' : 'bg-[#dfcef2] border-[#865bc6] text-[#865bc6] border-b-[#865bc6] hover:bg-purple-100']"
                            class="border-[4px] px-8 py-2.5 rounded-t-2xl font-black text-base transition-all relative top-[4px] z-30 transform active:scale-95"
                        >
                            모드
                        </button>
                        <button 
                            @click="activeTab = 'setting'"
                            :class="[activeTab === 'setting' ? 'bg-[#bfa2db] border-[#865bc6] text-white border-b-transparent' : 'bg-[#dfcef2] border-[#865bc6] text-[#865bc6] border-b-[#865bc6] hover:bg-purple-100']"
                            class="border-[4px] px-8 py-2.5 rounded-t-2xl font-black text-base transition-all relative top-[4px] z-30 transform active:scale-95"
                        >
                            설정
                        </button>
                        <button 
                            @click="activeTab = 'account'"
                            :class="[activeTab === 'account' ? 'bg-[#bfa2db] border-[#865bc6] text-white border-b-transparent' : 'bg-[#dfcef2] border-[#865bc6] text-[#865bc6] border-b-[#865bc6] hover:bg-purple-100']"
                            class="border-[4px] px-8 py-2.5 rounded-t-2xl font-black text-base transition-all relative top-[4px] z-30 transform active:scale-95"
                        >
                            계정
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
                                    <div v-if="selectedMode === mode.id && mode.active" class="w-6 h-6 bg-[#e953a8] rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            
                            <div v-else-if="activeTab === 'setting'" key="setting" class="flex-1 w-full flex items-center justify-center text-center text-white font-black text-2xl px-4">
                                사운드 및 시스템 설정이 추가될 예정입니다.
                            </div>

                            <div v-else-if="activeTab === 'account'" key="account" class="flex-1 w-full flex flex-col items-center justify-center p-4">
                                <div class="w-full max-w-md flex flex-col items-center select-none">
                                    
                                    <div class="flex items-center justify-center w-full max-w-[340px] h-35 mb-1 mr-5">
                                        <img src="/images/discord_logo_full.png" alt="Discord Full Logo" class="h-full w-auto object-contain" />
                                    </div>

                                    <div v-if="!auth.user" class="w-full flex flex-col items-center">
                                        <p class="text-center text-[#55328a] font-bold text-xl mb-6 leading-relaxed">
                                            디스코드 계정을 연동하여<br>
                                            게임을 더욱 즐겨보세요!
                                        </p>
                                        <button 
                                            @click="redirectToDiscordOAuth"
                                            class="w-full max-w-[340px] bg-[#6c527a] hover:bg-[#5b4368] border-b-4 border-[#4a3455] text-white font-black text-2xl py-4 rounded-2xl shadow-lg transition-all duration-150 transform active:scale-[0.98] tracking-wide"
                                        >
                                            계정 연동하기
                                        </button>
                                    </div>

                                    <div v-else class="w-full flex flex-col items-center">
                                        <div class="w-full max-w-[360px] text-center space-y-8 mb-10 pt-2">
                                            <div class="flex flex-col items-center">
                                                <span class="text-black font-black text-[27px] mb-2">연동된 계정:</span>
                                                <span class="text-black font-black text-[25px] tracking-normal break-all leading-tight">{{ auth.user.email || '연동 완료' }}</span>
                                            </div>
                                            <div class="flex flex-col items-center">
                                                <span class="text-black font-black text-[27px] mb-2">계정 아이디:</span>
                                                <span class="text-black font-black text-[30px] leading-none">
                                                    {{ auth.user.name }}{{ auth.user.discriminator && auth.user.discriminator !== '0' ? `#${auth.user.discriminator}` : '' }}
                                                </span>
                                            </div>
                                        </div>

                                        <button 
                                            @click="handleLogout"
                                            class="w-full max-w-[320px] bg-[#685370] hover:bg-[#584460] border-b-4 border-[#4a3850] text-white font-black text-3xl py-4 rounded-2xl shadow-lg transition-all duration-150 transform active:scale-[0.98] tracking-wider"
                                        >
                                            로그아웃
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>

                <div class="flex gap-4 w-full mt-6">
                    <button 
                        @click="handleJoinRoom"
                        :disabled="!isNicknameValid"
                        :class="[isNicknameValid ? 'bg-[#bfa2db] hover:bg-[#b090cf] border-[#865bc6] text-[#55328a] cursor-pointer active:scale-[0.98]' : 'bg-purple-300/50 border-purple-400 text-purple-400 opacity-60 cursor-not-allowed']"
                        class="flex-1 border-[4px] font-black text-2xl py-4 rounded-2xl shadow-md transition-all duration-200"
                    >
                        방 입장
                    </button>
                    <button 
                        @click="isModalOpen = true"
                        :disabled="!isNicknameValid"
                        :class="[isNicknameValid ? 'bg-[#bfa2db] hover:bg-[#b090cf] border-[#865bc6] text-[#55328a] cursor-pointer active:scale-[0.98]' : 'bg-purple-300/50 border-purple-400 text-purple-400 opacity-60 cursor-not-allowed']"
                        class="flex-1 border-[4px] font-black text-2xl py-4 rounded-2xl shadow-md transition-all duration-200"
                    >
                        방 생성
                    </button>
                </div>
            </div>
        </div>

        <LobbySetting 
            v-if="isModalOpen"
            :isOpen="isModalOpen" 
            @close="isModalOpen = false" 
            @confirm="handleCreateRoom" 
        />

    </div>
</template>

<style scoped>
@keyframes popIn {
    0% { transform: scale(0.97); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}
.animate-pop-in { animation: popIn 0.35s ease-out; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.fade-enter-from { opacity: 0; transform: translateY(4px); }
.fade-leave-to { opacity: 0; transform: translateY(-4px); }

.custom-scroll::-webkit-scrollbar { width: 8px; }
.custom-scroll::-webkit-scrollbar-track { background: transparent; }
.custom-scroll::-webkit-scrollbar-thumb { background-color: #865bc6; border-radius: 9999px; }
input::placeholder { color: rgba(255, 255, 255, 0.6); }
</style>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import LobbySetting from './lobbySetting.vue';
import ProfileCard from './ProfileCard.vue'; // ✅ 추가

const props = defineProps({
    defaultNickname: String,
    modes: Array
});

const page = usePage();

const auth = computed(() => page.props?.auth || { user: null });

const nickname = ref(props.defaultNickname || '');
const selectedMode = ref('normal');
const activeTab = ref('mode');

const fileInput = ref(null);
const profilePreview = ref('/images/profile.png');
const selectedFile = ref(null);

const isModalOpen = ref(false);
const isProfileCardOpen = ref(false); // ✅ 추가
const statusMessage = ref('');        // ✅ 추가

const syncAuthUser = () => {
    if (auth.value && auth.value.user) {
        console.log("============== 디스코드 데이터 주입 확인 ==============");
        console.log("유저 객체:", auth.value.user);
        console.log("아바타 URL:", auth.value.user.avatar_url);
        console.log("태그(Discriminator):", auth.value.user.discriminator);
        console.log("====================================================");
    }

    if (auth.value && auth.value.user) {
        nickname.value = auth.value.user.name || '';
        
        const userAvatar = auth.value.user.avatar_url;
        if (userAvatar) {
            profilePreview.value = userAvatar;
        } else {
            profilePreview.value = '/images/profile.png';
        }
    } else {
        nickname.value = props.defaultNickname || '';
        profilePreview.value = '/images/profile.png';
    }
};

onMounted(() => {
    syncAuthUser();
});

watch(auth, () => {
    syncAuthUser();
}, { deep: true, immediate: true });

const triggerFileInput = () => { fileInput.value.click(); };
const handleProfileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        selectedFile.value = file;
        profilePreview.value = URL.createObjectURL(file);
    }
};

const isNicknameValid = computed(() => {
    return nickname.value && nickname.value.trim().length > 0;
});

const handleJoinRoom = () => {
    const roomCode = prompt("입장할 코드를 입력하세요:");
    if(roomCode) alert(`방 코드 [${roomCode}]로 이동합니다.`);
};

const handleCreateRoom = (settings) => {
    isModalOpen.value = false;
    alert(`방 생성 완료!\n닉네임: ${nickname.value}\n모드: ${selectedMode.value}\n인원: ${settings.players}명\n제한시간: ${settings.timeLimit}초`);
};

const redirectToDiscordOAuth = () => {
    window.location.href = '/auth/discord/redirect';
};

const handleLogout = () => {
    if (confirm('로그아웃 하시겠습니까?')) {
        router.post('/logout');
    }
};
</script>