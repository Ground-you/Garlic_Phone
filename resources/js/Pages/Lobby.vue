<template>
    <Head title="대기실 - Garlic Phone" />

    <div class="min-h-screen bg-[#bfa2db] flex items-center justify-center p-4 relative overflow-hidden select-none">
        <div class="absolute inset-0 opacity-25" style="background-image: radial-gradient(#865bc6 2px, transparent 1.5px); background-size: 24px 24px;"></div>

        <div class="w-full max-w-6xl min-h-[660px] bg-[#c3addb] border-[4px] border-white/60 rounded-3xl p-6 shadow-xl relative z-10 flex flex-col justify-between">
            
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                
                <!-- 내 프로필 표시 -->
                <div class="flex items-center relative pt-4">
                    <div class="relative z-10 flex-shrink-0">
                        <!-- 호스트에게만 왕관 표시 -->
                        <span v-if="isHost" class="absolute -top-5 right-1 text-2xl drop-shadow-sm">👑</span>
                        <div class="w-20 h-20 rounded-full border-[6px] border-[#3cdb11] bg-white overflow-hidden shadow-md">
                            <img :src="userAvatar" alt="Avatar" class="w-full h-full object-cover" />
                        </div>
                    </div>
                    <div class="bg-[#c892db] border-[3px] border-[#8a429b] rounded-full pl-12 pr-6 py-2 -ml-8 shadow-sm">
                        <span class="text-white font-black text-lg tracking-wide">{{ userNickname }}</span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!-- ✅ 채팅 토글: 호스트만 볼 수 있음 -->
                    <div v-if="isHost" class="bg-[#a482cc] border-[3px] border-[#703b96] rounded-2xl px-4 py-2 flex items-center gap-3">
                        <span class="text-white font-black text-sm">채팅</span>
                        <button @click="isChatEnabled = !isChatEnabled" :class="isChatEnabled ? 'bg-[#703b96]' : 'bg-gray-400'" class="w-12 h-6 rounded-full relative p-0.5 transition-colors duration-200">
                            <div :class="isChatEnabled ? 'translate-x-6' : 'translate-x-0'" class="w-5 h-5 bg-white rounded-full shadow transform transition-transform duration-200"></div>
                        </button>
                    </div>
                    <div class="bg-[#a482cc] border-[3px] border-[#703b96] rounded-2xl px-5 py-2 text-white font-black text-sm">모드: {{ modeText }}</div>
                    <div class="bg-[#a482cc] border-[3px] border-[#703b96] rounded-2xl px-5 py-2 text-white font-black text-sm">시간: {{ timeLimit }}초</div>
                    <div class="bg-[#a482cc] border-[3px] border-[#703b96] rounded-2xl px-5 py-2 text-white font-black text-sm">인원: {{ currentCount }} / {{ players }}</div>
                </div>
            </div>

            <div class="flex-1 bg-[#a57cb8] border-[3px] border-[#703b96] rounded-2xl pt-10 p-5 relative flex flex-col lg:flex-row gap-5 mb-5 shadow-inner">
                <div class="absolute -top-[3px] left-6 bg-[#8a4a9e] border-x-[3px] border-b-[3px] border-[#703b96] text-white font-black text-base px-8 py-1.5 rounded-b-xl">
                    플레이어 목록
                </div>

                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3 content-start">
                    <div 
                        v-for="(slot, index) in Number(players)" 
                        :key="index"
                        :class="getSlotClass(index)"
                        class="border-[3px] rounded-2xl p-3 flex items-center justify-between min-h-[70px]"
                    >
                        <!-- ✅ 슬롯 0: 항상 방장 표시 -->
                        <template v-if="index === 0">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-full border-2 border-[#3cdb11] bg-white overflow-hidden">
                                    <img :src="hostDisplayAvatar" class="w-full h-full object-cover" />
                                </div>
                                <span class="font-black text-base text-white">{{ hostDisplayName }}</span>
                            </div>
                            <span class="bg-[#703b96] text-white font-black text-xs px-3 py-1 rounded-full border border-white/20">방장</span>
                        </template>

                        <!-- ✅ 슬롯 1: 게스트 본인 표시 -->
                        <template v-else-if="index === 1 && !isHost">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-full border-2 border-purple-400 bg-white overflow-hidden">
                                    <img :src="userAvatar" class="w-full h-full object-cover" />
                                </div>
                                <span class="font-black text-base text-white">{{ userNickname }}</span>
                            </div>
                            <span class="bg-[#3cdb11] text-white font-black text-xs px-3 py-1 rounded-full border border-white/20">나</span>
                        </template>

                        <!-- 빈 슬롯 -->
                        <template v-else>
                            <div class="flex items-center gap-3 opacity-60">
                                <div class="w-11 h-11 rounded-full bg-[#825694] flex items-center justify-center text-white/50">👤</div>
                                <span class="font-bold text-xs text-purple-100">방에 들어온 플레이어가 여기 표시 돼요.</span>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="w-full lg:w-[340px] bg-[#4a3559]/80 border-[3px] border-[#362342] rounded-2xl p-4 flex flex-col justify-between shadow-md">
                    <div class="flex-1 flex flex-col justify-end min-h-[240px] mb-3">
                        <div class="text-purple-200/50 text-center font-bold text-xs select-none">여기에 채팅을 입력하세요.</div>
                    </div>
                    <input type="text" placeholder="채팅을 입력해 주세요..." :disabled="!isChatEnabled" class="w-full bg-[#362342] border-2 border-[#5c3e6e] rounded-xl px-3 py-2 text-white font-bold text-xs outline-none focus:border-[#c892db] disabled:opacity-30" />
                </div>
            </div>

            <!-- 방장 버튼 -->
            <div v-if="isHost" class="flex flex-wrap gap-4 w-full">
                <button @click="isInviteModalOpen = true" class="flex-1 min-w-[130px] bg-[#d3aade] hover:bg-[#c39ac7] border-[3px] border-[#703b96] text-[#42215c] font-black text-base py-3 rounded-xl transition-transform active:scale-95">
                    플레이어 초대
                </button>
                <button @click="leaveOrDisbandLobby" class="flex-1 min-w-[130px] bg-[#d3aade] hover:bg-[#c39ac7] border-[3px] border-[#703b96] text-[#42215c] font-black text-base py-3 rounded-xl transition-transform active:scale-95">
                    방 해체
                </button>
                <div class="flex-1 min-w-[150px] bg-[#bfa2db]/40 border-[3px] border-[#703b96] text-[#42215c] font-black text-base py-3 rounded-xl flex items-center justify-center shadow-inner">
                    준비된 인원: 1 / 1
                </div>
                <button class="flex-1 min-w-[130px] bg-[#d3aade] hover:bg-[#c39ac7] border-[3px] border-[#703b96] text-[#42215c] font-black text-base py-3 rounded-xl transition-transform active:scale-95">
                    시작
                </button>
            </div>

            <!-- 게스트 버튼 -->
            <div v-else class="flex flex-wrap gap-4 w-full">
                <button @click="leaveOrDisbandLobby" class="flex-1 min-w-[130px] bg-red-400 hover:bg-red-500 border-[3px] border-red-700 text-white font-black text-base py-3 rounded-xl transition-transform active:scale-95">
                    나가기
                </button>
                <div class="flex-1 min-w-[150px] bg-[#bfa2db]/40 border-[3px] border-[#703b96] text-[#42215c] font-black text-base py-3 rounded-xl flex items-center justify-center shadow-inner">
                    준비된 인원: {{ isReady ? '2' : '1' }} / {{ currentCount }}
                </div>
                <button @click="isReady = !isReady" :class="isReady ? 'bg-green-500 text-white border-green-700' : 'bg-[#d3aade] text-[#42215c]'" class="flex-1 min-w-[130px] border-[3px] border-[#703b96] font-black text-base py-3 rounded-xl transition-transform active:scale-95">
                    {{ isReady ? '준비 완료!' : '준비' }}
                </button>
            </div>
        </div>

        <!-- 초대 모달 -->
        <div v-if="isInviteModalOpen" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4">
            <div class="w-full max-w-xl bg-[#c59ad4] border-[4px] border-[#7a3e8c] rounded-2xl shadow-2xl relative flex flex-col items-center p-6">
                <div class="absolute -top-[22px] bg-[#9146a1] border-[3px] border-[#7a3e8c] text-white font-black text-sm px-6 py-1.5 rounded-xl">
                    어떤 방식으로 플레이어를 초대할까요?
                </div>
                <div class="w-full grid grid-cols-1 sm:grid-cols-2 gap-4 my-6 mt-8">
                    <div class="flex flex-col gap-3">
                        <!-- ✅ 실제 lobbyId를 초대코드로 사용 -->
                        <button @click="generateInviteCode" class="w-full bg-white border-[3px] border-[#7a3e8c] hover:bg-gray-50 text-gray-800 font-black py-3.5 px-4 rounded-xl text-sm transition-all active:scale-95">
                            초대 코드 생성
                        </button>
                        <button class="w-full bg-white border-[3px] border-[#7a3e8c] opacity-50 cursor-not-allowed text-gray-400 font-black py-3.5 px-4 rounded-xl text-sm">초대 링크 생성</button>
                        <button class="w-full bg-white border-[3px] border-[#7a3e8c] opacity-50 cursor-not-allowed text-gray-400 font-black py-3.5 px-4 rounded-xl text-sm">초대 QR 생성</button>
                    </div>
                    <div class="bg-[#5b3b6b] border-[3px] border-[#3f224f] rounded-xl p-4 flex flex-col items-center justify-center text-center min-h-[180px]">
                        <template v-if="generatedInviteCode">
                            <span class="text-purple-200 text-xs font-bold mb-1">생성된 초대 코드</span>
                            <div class="text-white text-3xl font-black tracking-widest bg-[#3f224f] px-5 py-2.5 rounded-lg border border-purple-400/30 select-all">
                                {{ generatedInviteCode }}
                            </div>
                            <button @click="copyInviteCode" class="mt-3 text-purple-300 text-xs font-bold hover:text-white transition">
                                {{ copied ? '✅ 복사됨' : '📋 클립보드에 복사' }}
                            </button>
                        </template>
                        <template v-else>
                            <p class="text-purple-200/60 font-black text-sm">여기에 생성 됩니다.</p>
                        </template>
                    </div>
                </div>
                <button @click="isInviteModalOpen = false" class="bg-[#a865b8] border-[3px] border-[#693575] text-white font-black text-base px-10 py-2 rounded-xl">확인</button>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';

const props = defineProps({
    lobbyId: [String, Number],
    nickname: String,
    // ✅ avatar prop 추가: 홈에서 설정한 프로필 이미지
    avatar: { type: String, default: '/images/profile.png' },
    // ✅ 게스트가 방장 정보를 표시하기 위한 props (백엔드에서 전달 필요)
    hostNickname: { type: String, default: '' },
    hostAvatar: { type: String, default: '/images/profile.png' },
    mode: String,
    players: { type: [String, Number], default: 8 },
    timeLimit: { type: [String, Number], default: 40 },
    // ✅ isHost 기본값 false로 변경 (방 생성자만 true, 입장자는 false)
    isHost: { type: Boolean, default: false },
});

const page = usePage();
const isChatEnabled = ref(true);
const isInviteModalOpen = ref(false);
const generatedInviteCode = ref('');
const isReady = ref(false);
const copied = ref(false);

// ✅ 내 닉네임: prop 우선, 없으면 auth 유저명
const userNickname = computed(() =>
    props.nickname || page.props?.auth?.user?.name || '플레이어'
);

// ✅ 내 아바타: Discord 아바타 > prop으로 받은 아바타 > 기본 이미지
const userAvatar = computed(() =>
    page.props?.auth?.user?.avatar_url || props.avatar || '/images/profile.png'
);

// ✅ 슬롯 0 방장 표시 (내가 방장이면 내 정보, 게스트면 백엔드에서 받은 호스트 정보)
const hostDisplayName = computed(() =>
    props.isHost ? userNickname.value : (props.hostNickname || '방장')
);
const hostDisplayAvatar = computed(() =>
    props.isHost ? userAvatar.value : (props.hostAvatar || '/images/profile.png')
);

const modeText = computed(() => props.mode === 'normal' ? '일반' : props.mode || '일반');
const currentCount = computed(() => props.isHost ? 1 : 2);

const getSlotClass = (index) => {
    if (index === 0) return 'bg-[#b682c7] border-white shadow-md';
    if (index === 1 && !props.isHost) return 'bg-[#a2c782] border-white shadow-md';
    return 'bg-[#956ca6]/40 border-dashed border-[#865996]';
};

// ✅ 실제 lobbyId를 초대 코드로 사용
const generateInviteCode = () => {
    generatedInviteCode.value = String(props.lobbyId);
};

const copyInviteCode = async () => {
    try {
        await navigator.clipboard.writeText(generatedInviteCode.value);
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2000);
    } catch {
        copied.value = false;
    }
};

const leaveOrDisbandLobby = () => {
    const message = props.isHost ? '방을 해체하고 대기실을 나가시겠습니까?' : '대기실에서 나가시겠습니까?';
    if (confirm(message)) {
        router.visit('/');
    }
};
</script>