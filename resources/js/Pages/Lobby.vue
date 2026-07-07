<template>
    <Head title="대기실 - Garlic Phone" />

    <div class="min-h-screen bg-[#bfa2db] flex items-center justify-center p-4 relative overflow-hidden select-none">
        <div class="absolute inset-0 opacity-25" style="background-image: radial-gradient(#865bc6 2px, transparent 1.5px); background-size: 24px 24px;"></div>

        <div class="w-full max-w-6xl min-h-[660px] bg-[#c3addb] border-[4px] border-white/60 rounded-3xl p-6 shadow-xl relative z-10 flex flex-col justify-between">

            <!-- 상단: 내 프로필 + 방 설정 -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">

                <div class="flex items-center relative pt-4">
                    <div class="relative z-10 flex-shrink-0">
                        <span v-if="isHost" class="absolute -top-5 right-1 text-2xl drop-shadow-sm">👑</span>
                        <div class="w-20 h-20 rounded-full border-[4px] border-[#703b96] bg-white overflow-hidden shadow-md">
                            <img :src="userAvatar" alt="Avatar" class="w-full h-full object-cover" />
                        </div>
                    </div>
                    <div class="bg-[#c892db] border-[3px] border-[#8a429b] rounded-full pl-12 pr-6 py-2 -ml-8 shadow-sm">
                        <span class="text-white font-black text-lg tracking-wide">{{ userNickname }}</span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!-- 채팅 토글: 방장만 -->
                    <div v-if="isHost" class="bg-[#a482cc] border-[3px] border-[#703b96] rounded-2xl px-4 py-2 flex items-center gap-3">
                        <span class="text-white font-black text-sm">채팅</span>
                        <button
                            @click="handleChatToggle"
                            :class="isChatEnabled ? 'bg-[#703b96]' : 'bg-gray-400'"
                            class="w-12 h-6 rounded-full relative p-0.5 transition-colors duration-150"
                        >
                            <div
                                :class="isChatEnabled ? 'translate-x-6' : 'translate-x-0'"
                                class="w-5 h-5 bg-white rounded-full shadow transform transition-transform duration-150"
                            ></div>
                        </button>
                    </div>
                    <div class="bg-[#a482cc] border-[3px] border-[#703b96] rounded-2xl px-5 py-2 text-white font-black text-sm">모드: {{ modeText }}</div>
                    <div class="bg-[#a482cc] border-[3px] border-[#703b96] rounded-2xl px-5 py-2 text-white font-black text-sm">시간: {{ timeLimit }}초</div>
                    <div class="bg-[#a482cc] border-[3px] border-[#703b96] rounded-2xl px-5 py-2 text-white font-black text-sm">인원: {{ currentCount }} / {{ players }}</div>
                </div>
            </div>

            <!-- 메인: 플레이어 목록 + 채팅 -->
            <div class="flex-1 bg-[#a57cb8] border-[3px] border-[#703b96] rounded-2xl pt-10 p-5 relative flex flex-col lg:flex-row gap-5 mb-5 shadow-inner">
                <div class="absolute -top-[3px] left-6 bg-[#8a4a9e] border-x-[3px] border-b-[3px] border-[#703b96] text-white font-black text-base px-8 py-1.5 rounded-b-xl">
                    플레이어 목록
                </div>

                <!-- 플레이어 슬롯 -->
                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3 content-start">
                    <div
                        v-for="(slot, index) in Number(players)"
                        :key="index"
                        :class="getSlotClass(index)"
                        class="border-[3px] rounded-2xl p-3 flex items-center justify-between min-h-[70px]"
                    >
                        <template v-if="activePlayers[index]">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-11 h-11 rounded-full border-2 bg-white overflow-hidden"
                                    :class="activePlayers[index].is_host ? 'border-[#3cdb11]' : 'border-purple-400'"
                                >
                                    <img :src="activePlayers[index].avatar || '/images/profile.png'" class="w-full h-full object-cover" />
                                </div>
                                <span class="font-black text-base text-white">{{ activePlayers[index].nickname }}</span>
                            </div>
                            <span
                                v-if="activePlayers[index].is_host"
                                class="bg-[#703b96] text-white font-black text-xs px-3 py-1 rounded-full border border-white/20"
                            >방장</span>
                            <span
                                v-else-if="activePlayers[index].session_id === mySessionId"
                                class="bg-[#3cdb11] text-white font-black text-xs px-3 py-1 rounded-full border border-white/20"
                            >나</span>
                        </template>
                        <template v-else>
                            <div class="flex items-center gap-3 opacity-60">
                                <div class="w-11 h-11 rounded-full bg-[#825694] flex items-center justify-center text-white/50">👤</div>
                                <span class="font-bold text-xs text-purple-100">방에 들어온 플레이어가 여기 표시 돼요.</span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- 채팅창 -->
                <div class="w-full lg:w-[340px] bg-[#4a3559]/80 border-[3px] border-[#362342] rounded-2xl p-4 flex flex-col shadow-md">
                    <!-- 메시지 목록 -->
                    <div
                        ref="chatContainer"
                        class="flex-1 flex flex-col gap-2 min-h-[240px] max-h-[300px] overflow-y-auto mb-3 pr-1 custom-scroll"
                    >
                        <div v-if="messages.length === 0" class="flex-1 flex items-end justify-center">
                            <div class="text-purple-200/50 text-center font-bold text-xs select-none">여기에 채팅이 표시됩니다.</div>
                        </div>
                        <div v-for="(msg, i) in messages" :key="i" class="flex items-start gap-2">
                            <div class="w-7 h-7 rounded-full border border-purple-400/50 overflow-hidden shrink-0">
                                <img :src="msg.avatar || '/images/profile.png'" class="w-full h-full object-cover" />
                            </div>
                            <div class="flex flex-col max-w-[80%]">
                                <span class="text-purple-300 text-[10px] font-bold">{{ msg.nickname }} <span class="opacity-60">{{ msg.time }}</span></span>
                                <span class="text-white text-xs font-semibold bg-[#5c3e6e]/60 px-2 py-1 rounded-xl break-words">{{ msg.message }}</span>
                            </div>
                        </div>
                    </div>
                    <!-- 채팅 입력 -->
                    <div class="flex gap-2">
                        <input
                            v-model="chatInput"
                            @keyup.enter="sendMessage"
                            type="text"
                            placeholder="채팅을 입력해 주세요..."
                            :disabled="!isChatEnabled"
                            maxlength="200"
                            class="flex-1 bg-[#362342] border-2 border-[#5c3e6e] rounded-xl px-3 py-2 text-white font-bold text-xs outline-none focus:border-[#c892db] disabled:opacity-30 transition-colors"
                        />
                        <button
                            @click="sendMessage"
                            :disabled="!isChatEnabled || !chatInput.trim()"
                            class="bg-[#703b96] hover:bg-[#5c2e80] text-white text-xs font-black px-3 py-2 rounded-xl disabled:opacity-30 transition active:scale-95"
                            style="transition: background-color 60ms, transform 60ms;"
                        >전송</button>
                    </div>
                </div>
            </div>

            <!-- 방장 버튼 -->
            <div v-if="isHost" class="flex flex-wrap gap-4 w-full">
                <button
                    @click="isInviteModalOpen = true"
                    class="flex-1 min-w-[130px] bg-[#d3aade] hover:bg-[#c39ac7] border-[3px] border-[#703b96] text-[#42215c] font-black text-base py-3 rounded-xl transition-transform active:scale-95"
                >플레이어 초대</button>
                <button
                    @click="leaveOrDisbandLobby"
                    class="flex-1 min-w-[130px] bg-[#d3aade] hover:bg-[#c39ac7] border-[3px] border-[#703b96] text-[#42215c] font-black text-base py-3 rounded-xl transition-transform active:scale-95"
                >방 해체</button>
                <div class="flex-1 min-w-[150px] bg-[#bfa2db]/40 border-[3px] border-[#703b96] text-[#42215c] font-black text-base py-3 rounded-xl flex items-center justify-center shadow-inner">
                    준비된 인원: {{ currentCount }} / {{ currentCount }}
                </div>
                <button class="flex-1 min-w-[130px] bg-[#d3aade] hover:bg-[#c39ac7] border-[3px] border-[#703b96] text-[#42215c] font-black text-base py-3 rounded-xl transition-transform active:scale-95">
                    시작
                </button>
            </div>

            <!-- 게스트 버튼 -->
            <div v-else class="flex flex-wrap gap-4 w-full">
                <button
                    @click="leaveOrDisbandLobby"
                    class="flex-1 min-w-[130px] bg-red-400 hover:bg-red-500 border-[3px] border-red-700 text-white font-black text-base py-3 rounded-xl transition-transform active:scale-95"
                >나가기</button>
                <div class="flex-1 min-w-[150px] bg-[#bfa2db]/40 border-[3px] border-[#703b96] text-[#42215c] font-black text-base py-3 rounded-xl flex items-center justify-center shadow-inner">
                    준비된 인원: {{ readyCount }} / {{ currentCount }}
                </div>
                <button
                    @click="toggleReady"
                    :class="isReady ? 'bg-green-500 text-white border-green-700' : 'bg-[#d3aade] text-[#42215c] border-[#703b96]'"
                    class="flex-1 min-w-[130px] border-[3px] font-black text-base py-3 rounded-xl transition-transform active:scale-95"
                >{{ isReady ? '준비 완료!' : '준비' }}</button>
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
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';

const props = defineProps({
    lobbyCode:      { type: String, required: true },
    lobbyId:        [String, Number],
    mySessionId:    { type: String, default: '' },
    nickname:       String,
    avatar:         { type: String, default: '/images/profile.png' },
    hostNickname:   String,
    hostAvatar:     { type: String, default: '/images/profile.png' },
    mode:           String,
    players:        { type: [String, Number], default: 8 },
    timeLimit:      { type: [String, Number], default: 40 },
    isHost:         { type: Boolean, default: false },
    chatEnabled:    { type: Boolean, default: true },
    initialPlayers: { type: Array, default: () => [] },
});

const page = usePage();

const isChatEnabled  = ref(props.chatEnabled);
const isInviteModalOpen = ref(false);
const generatedInviteCode = ref('');
const isReady        = ref(false);
const copied         = ref(false);
const chatInput      = ref('');
const chatContainer  = ref(null);

// 실시간 플레이어 목록 (DB initial + Echo 실시간 업데이트)
const activePlayers = ref([...props.initialPlayers]);
const messages      = ref([]);

const userNickname = computed(() =>
    props.nickname || page.props?.auth?.user?.name || '플레이어'
);
const userAvatar = computed(() =>
    page.props?.auth?.user?.avatar_url || props.avatar || '/images/profile.png'
);
const modeText = computed(() =>
    props.mode === 'normal' ? '일반' : props.mode || '일반'
);
const currentCount = computed(() => activePlayers.value.length);
const readyCount   = computed(() =>
    activePlayers.value.filter(p => p.is_ready || p.is_host).length
);

const getSlotClass = (index) => {
    const p = activePlayers.value[index];
    if (!p) return 'bg-[#956ca6]/40 border-dashed border-[#865996]';
    return p.is_host
        ? 'bg-[#b682c7] border-white shadow-md'
        : 'bg-[#a2c782] border-white shadow-md';
};

// ── Echo 실시간 구독 ─────────────────────────────────
let echoChannel = null;

onMounted(() => {
    echoChannel = window.Echo.channel(`lobby.${props.lobbyCode}`)

        // 새 플레이어 입장
        .listen('.player.joined', (e) => {
            if (!activePlayers.value.find(p => p.session_id === e.session_id)) {
                activePlayers.value.push({
                    nickname:   e.nickname,
                    avatar:     e.avatar || '/images/profile.png',
                    is_host:    e.is_host,
                    is_ready:   false,
                    session_id: e.session_id,
                });
            }
        })

        // 플레이어 퇴장
        .listen('.player.left', (e) => {
            activePlayers.value = activePlayers.value.filter(
                p => p.session_id !== e.session_id
            );
        })

        // 채팅 메시지 수신
        .listen('.chat.message', async (e) => {
            messages.value.push(e);
            await nextTick();
            if (chatContainer.value) {
                chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
            }
        })

        // 채팅 ON/OFF 수신 (게스트 화면 동기화)
        .listen('.chat.toggled', (e) => {
            isChatEnabled.value = e.enabled;
        });

    // 브라우저 닫힐 때 퇴장 처리
    window.addEventListener('beforeunload', handleBeforeUnload);
});

onUnmounted(() => {
    window.Echo.leaveChannel(`lobby.${props.lobbyCode}`);
    window.removeEventListener('beforeunload', handleBeforeUnload);
});

const handleBeforeUnload = () => {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    navigator.sendBeacon(
        `/lobby/${props.lobbyCode}/leave`,
        new Blob(
            [JSON.stringify({ isHost: props.isHost, _token: token })],
            { type: 'application/json' }
        )
    );
};

// ── 채팅 전송 ─────────────────────────────────────────
const sendMessage = async () => {
    const msg = chatInput.value.trim();
    if (!msg || !isChatEnabled.value) return;
    chatInput.value = '';

    try {
        await window.axios.post(`/lobby/${props.lobbyCode}/chat`, {
            message:  msg,
            nickname: userNickname.value,
            avatar:   userAvatar.value,
        });
    } catch (e) {
        console.error('채팅 전송 실패:', e);
        chatInput.value = msg; // 실패 시 복원
    }
};

// ── 채팅 토글 (방장만) ───────────────────────────────
const handleChatToggle = async () => {
    const newState = !isChatEnabled.value;
    isChatEnabled.value = newState; // 즉각 UI 반영

    try {
        await window.axios.patch(`/lobby/${props.lobbyCode}/toggle-chat`, {
            enabled: newState,
        });
    } catch (e) {
        isChatEnabled.value = !newState; // 실패 시 롤백
        console.error('채팅 토글 실패:', e);
    }
};

// ── 초대 코드 ─────────────────────────────────────────
const generateInviteCode = () => {
    generatedInviteCode.value = props.lobbyCode;
};

const copyInviteCode = async () => {
    try {
        await navigator.clipboard.writeText(generatedInviteCode.value);
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2000);
    } catch { copied.value = false; }
};

// ── 준비 ──────────────────────────────────────────────
const toggleReady = () => { isReady.value = !isReady.value; };

// ── 퇴장 / 방 해체 ───────────────────────────────────
const leaveOrDisbandLobby = async () => {
    const msg = props.isHost
        ? '방을 해체하고 나가시겠습니까?'
        : '대기실에서 나가시겠습니까?';
    if (!confirm(msg)) return;

    try {
        await window.axios.delete(`/lobby/${props.lobbyCode}/leave`, {
            data: { isHost: props.isHost },
        });
    } finally {
        router.visit('/');
    }
};
</script>

<style scoped>
.custom-scroll::-webkit-scrollbar { width: 6px; }
.custom-scroll::-webkit-scrollbar-track { background: transparent; }
.custom-scroll::-webkit-scrollbar-thumb { background-color: #865bc6; border-radius: 9999px; }
</style>