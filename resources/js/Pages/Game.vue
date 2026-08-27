<template>
    <Head title="게임 - Garlic Phone" />

    <div class="min-h-screen bg-[#bfa2db] flex items-center justify-center p-4 relative overflow-hidden select-none">
        <div class="absolute inset-0 opacity-25" style="background-image: radial-gradient(#865bc6 2px, transparent 1.5px); background-size: 24px 24px;"></div>

        <div class="w-full max-w-6xl min-h-[560px] bg-[#c3addb] border-[4px] border-white/60 rounded-3xl p-6 shadow-xl relative z-10 flex flex-col justify-between">

            <!-- 상단: 내 프로필 + 상태 뱃지 -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div class="flex items-center relative pt-4">
                    <div class="relative z-10 flex-shrink-0">
                        <span v-if="isHost" class="absolute -top-5 right-1 text-2xl drop-shadow-sm">👑</span>
                        <div class="w-16 h-16 rounded-full border-[4px] border-[#703b96] bg-white overflow-hidden shadow-md">
                            <img :src="avatar" class="w-full h-full object-cover" />
                        </div>
                    </div>
                    <div class="bg-[#c892db] border-[3px] border-[#8a429b] rounded-full pl-10 pr-6 py-2 -ml-6 shadow-sm">
                        <span class="text-white font-black text-base tracking-wide">{{ nickname }}</span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="bg-[#a482cc] border-[3px] border-[#703b96] rounded-2xl px-5 py-2 text-white font-black text-sm">모드: {{ modeText }}</div>
                    <div class="bg-[#a482cc] border-[3px] border-[#703b96] rounded-2xl px-5 py-2 text-white font-black text-sm">시간: {{ timeLimit }}초</div>
                    <div class="bg-[#a482cc] border-[3px] border-[#703b96] rounded-2xl px-5 py-2 text-white font-black text-sm">인원: {{ submittedCount }} / {{ maxPlayers }}</div>
                </div>
            </div>

            <!-- 본문: 플레이어 목록 + 주제 정하기 -->
            <div class="flex-1 flex flex-col lg:flex-row gap-5 mb-5">

                <!-- 플레이어 목록 -->
                <div class="lg:w-[300px] bg-[#a57cb8] border-[3px] border-[#703b96] rounded-2xl pt-10 p-4 relative shadow-inner">
                    <div class="absolute -top-[3px] left-6 bg-[#8a4a9e] border-x-[3px] border-b-[3px] border-[#703b96] text-white font-black text-sm px-6 py-1.5 rounded-b-xl">
                        플레이어 목록
                    </div>
                    <div class="flex flex-col gap-2 max-h-[320px] overflow-y-auto custom-scroll pr-1">
                        <div v-for="p in players" :key="p.session_id"
                             class="bg-[#9e82b8] border-2 border-purple-300 rounded-xl p-2 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-9 h-9 rounded-full border-2 border-white/40 overflow-hidden bg-white">
                                    <img :src="p.avatar || '/images/profile.png'" class="w-full h-full object-cover" />
                                </div>
                                <span class="font-bold text-sm text-white">{{ p.nickname }}</span>
                            </div>
                            <span v-if="submittedSessions.has(p.session_id)" class="bg-green-500 text-white text-[10px] font-black px-2 py-1 rounded-full">완료</span>
                            <span v-else class="bg-white/20 text-white/70 text-[10px] font-black px-2 py-1 rounded-full">대기중</span>
                        </div>
                    </div>
                </div>

                <!-- 주제 정하기 -->
                <div class="flex-1 bg-[#a57cb8] border-[3px] border-[#703b96] rounded-2xl pt-10 p-6 relative shadow-inner flex flex-col items-center">
                    <div class="absolute -top-[3px] left-6 bg-[#8a4a9e] border-x-[3px] border-b-[3px] border-[#703b96] text-white font-black text-sm px-6 py-1.5 rounded-b-xl">
                        주제 정하기
                    </div>

                    <!-- 마스코트 -->
                    <div class="relative mt-2 mb-4">
                        <svg viewBox="0 0 100 110" class="w-24 h-24 drop-shadow-md">
                            <ellipse cx="50" cy="65" rx="32" ry="38" fill="#ffffff" stroke="#c9a0dc" stroke-width="4"/>
                            <path d="M35 30 Q50 5 65 30" fill="none" stroke="#c9a0dc" stroke-width="4" stroke-linecap="round"/>
                            <circle cx="40" cy="65" r="4" fill="#5c2e80"/>
                            <circle cx="60" cy="65" r="4" fill="#5c2e80"/>
                            <path d="M42 78 Q50 84 58 78" fill="none" stroke="#5c2e80" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                        <div class="absolute -top-3 -right-6 bg-white border-2 border-[#c9a0dc] rounded-full w-9 h-9 flex items-center justify-center shadow">
                            <span class="text-[#8a429b] font-black">?</span>
                        </div>
                    </div>

                    <p class="text-[#42215c] font-black text-sm text-center">다음 플레이어에게 넘겨줄 주제를 선정해 보아요.</p>
                    <p class="text-[#6b3f80] font-bold text-xs text-center mb-4">EX) 산타를 하는 루돌프</p>

                    <textarea
                        v-model="topicInput"
                        :disabled="hasSubmitted"
                        maxlength="60"
                        placeholder="주제를 적어주세요. 공백으로 넘기면 자동으로 정해집니다."
                        class="w-full max-w-md bg-white/90 border-[3px] border-[#8a429b] rounded-2xl px-4 py-3 text-sm font-bold text-[#42215c] outline-none focus:border-[#5c2e80] disabled:opacity-50 resize-none h-20"
                    ></textarea>
                </div>
            </div>

            <!-- 하단: 완료 현황 + 타이머 + 완료 버튼 -->
            <div class="flex flex-wrap gap-4 w-full">
                <div class="flex-1 min-w-[150px] bg-[#bfa2db]/40 border-[3px] border-[#703b96] text-[#42215c] font-black text-base py-3 rounded-xl flex items-center justify-center shadow-inner">
                    완료된 플레이어: {{ submittedCount }} / {{ maxPlayers }}
                </div>
                <div class="flex-1 min-w-[150px] bg-[#bfa2db]/40 border-[3px] border-[#703b96] text-[#42215c] font-black text-base py-3 rounded-xl flex items-center justify-center shadow-inner">
                    남은 시간: {{ timeLeft }} / {{ timeLimit }}
                </div>
                <button
                    @click="submitTopic"
                    :disabled="hasSubmitted"
                    class="flex-1 min-w-[130px] bg-[#d3aade] hover:bg-[#c39ac7] border-[3px] border-[#703b96] text-[#42215c] font-black text-base py-3 rounded-xl transition-transform active:scale-95 disabled:opacity-50"
                >
                    {{ hasSubmitted ? '제출 완료!' : '완료' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    lobbyCode:      { type: String, required: true },
    mySessionId:    { type: String, default: '' },
    nickname:       { type: String, default: '플레이어' },
    avatar:         { type: String, default: '/images/profile.png' },
    isHost:         { type: Boolean, default: false },
    mode:           { type: String, default: 'normal' },
    maxPlayers:     { type: Number, default: 8 },
    timeLimit:      { type: Number, default: 40 },
    players:        { type: Array, default: () => [] },
    submittedCount: { type: Number, default: 0 },
    submittedSessions: { type: Array, default: () => [] },
});

const modeText        = computed(() => props.mode === 'normal' ? '일반' : props.mode);
const topicInput       = ref('');
const hasSubmitted     = ref(false);
const submittedCount   = ref(props.submittedCount);
const submittedSessions = ref(new Set(props.submittedSessions));
const timeLeft         = ref(props.timeLimit);

let timerInterval = null;
let echoChannel = null;

onMounted(() => {
    timerInterval = setInterval(() => {
        if (timeLeft.value > 0) timeLeft.value--;
        if (timeLeft.value === 0 && !hasSubmitted.value) submitTopic();
    }, 1000);

    echoChannel = window.Echo.channel(`game.${props.lobbyCode}`)
        .listen('.topic.submitted', (e) => {
            submittedCount.value = e.submitted_count;
            submittedSessions.value.add(e.session_id);
        });
});

onUnmounted(() => {
    clearInterval(timerInterval);
    window.Echo.leaveChannel(`game.${props.lobbyCode}`);
});

const submitTopic = async () => {
    if (hasSubmitted.value) return;
    hasSubmitted.value = true;
    try {
        const res = await window.axios.post(`/game/${props.lobbyCode}/submit-topic`, {
            content: topicInput.value.trim(),
        });
        submittedCount.value = res.data.submitted;
        submittedSessions.value.add(props.mySessionId);
    } catch (e) {
        hasSubmitted.value = false;
        console.error('주제 제출 실패:', e);
    }
};
</script>

<style scoped>
.custom-scroll::-webkit-scrollbar { width: 6px; }
.custom-scroll::-webkit-scrollbar-track { background: transparent; }
.custom-scroll::-webkit-scrollbar-thumb { background-color: #865bc6; border-radius: 9999px; }
</style>