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
                    <div class="bg-[#a482cc] border-[3px] border-[#703b96] rounded-2xl px-5 py-2 text-white font-black text-sm">라운드: {{ round.round + 1 }} / {{ round.totalRounds }}</div>
                </div>
            </div>

            <!-- 게임 종료 화면 -->
            <div v-if="round.finished" class="flex-1 flex flex-col items-center gap-4 text-white overflow-hidden">
                <p class="font-black text-xl mt-2">🎉 결과 보기</p>

                <div v-if="chains.length" class="w-full flex flex-col items-center gap-3 flex-1 overflow-hidden">
                    <!-- 체인 선택 탭 -->
                    <div class="flex gap-2 flex-wrap justify-center">
                        <button v-for="(chain, i) in chains" :key="i"
                            @click="activeChain = i"
                            :class="activeChain === i ? 'bg-white text-[#42215c]' : 'bg-white/20 text-white'"
                            class="px-4 py-1.5 rounded-full font-black text-xs transition">
                            {{ chain.starter }}의 체인
                        </button>
                    </div>

                    <!-- 선택된 체인의 스텝들 -->
                    <div class="flex-1 w-full overflow-y-auto custom-scroll px-2">
                        <div class="flex flex-col gap-3 max-w-md mx-auto">
                            <div v-for="step in chains[activeChain].steps" :key="step.round"
                                class="bg-[#a57cb8] border-2 border-[#703b96] rounded-2xl p-3">
                                <p class="text-purple-200 text-[10px] font-bold mb-1">{{ step.author }} · {{ step.round + 1 }}번째</p>
                                <p v-if="step.type === 'text'" class="text-white font-bold text-sm">{{ step.content }}</p>
                                <img v-else :src="step.content" class="w-full rounded-xl bg-white" />
                            </div>
                        </div>
                    </div>
                </div>

                <button v-if="isHost" @click="returnToLobby"
                    class="bg-[#d3aade] hover:bg-[#c39ac7] border-[3px] border-[#703b96] text-[#42215c] font-black text-base px-8 py-3 rounded-xl transition-transform active:scale-95 mb-2">
                    대기방으로 돌아가기
                </button>
                <p v-else class="text-white/70 font-bold text-xs mb-2">방장이 대기방으로 이동시킬 때까지 기다려 주세요.</p>
            </div>

            <!-- 본문: 플레이어 목록 + 라운드 콘텐츠 -->
            <div v-else class="flex-1 flex flex-col lg:flex-row gap-5 mb-5">

                <!-- 플레이어 목록 -->
                <div class="lg:w-[280px] bg-[#a57cb8] border-[3px] border-[#703b96] rounded-2xl pt-10 p-4 relative shadow-inner">
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

                <!-- 라운드 콘텐츠: 텍스트 입력 -->
                <div v-if="round.type === 'text'" class="flex-1 bg-[#a57cb8] border-[3px] border-[#703b96] rounded-2xl pt-10 p-6 relative shadow-inner flex flex-col items-center">
                    <div class="absolute -top-[3px] left-6 bg-[#8a4a9e] border-x-[3px] border-b-[3px] border-[#703b96] text-white font-black text-sm px-6 py-1.5 rounded-b-xl">
                        {{ round.round === 0 ? '주제 정하기' : '설명 작성하기' }}
                    </div>

                    <!-- 0라운드가 아니면 이전 사람 그림을 보여줌 -->
                    <div v-if="round.previousContent" class="w-full max-w-md mb-4 mt-4">
                        <p class="text-[#42215c] font-bold text-xs text-center mb-2">이 그림을 보고 설명을 적어주세요</p>
                        <img :src="round.previousContent" class="w-full border-[3px] border-[#8a429b] rounded-2xl bg-white" />
                    </div>
                    <template v-else>
                        <p class="text-[#42215c] font-black text-sm text-center mt-4">다음 플레이어에게 넘겨줄 주제를 선정해 보아요.</p>
                        <p class="text-[#6b3f80] font-bold text-xs text-center mb-4">EX) 산타를 타는 루돌프</p>
                    </template>

                    <textarea
                        v-model="textInput"
                        :disabled="round.hasSubmitted"
                        maxlength="60"
                        placeholder="내용을 적어주세요. 공백으로 넘기면 자동으로 정해집니다."
                        class="w-full max-w-md bg-white/90 border-[3px] border-[#8a429b] rounded-2xl px-4 py-3 text-sm font-bold text-[#42215c] outline-none focus:border-[#5c2e80] disabled:opacity-50 resize-none h-20"
                    ></textarea>
                </div>

                <!-- 라운드 콘텐츠: 그림 그리기 -->
                <div v-else class="flex-1 bg-[#a57cb8] border-[3px] border-[#703b96] rounded-2xl pt-10 p-4 relative shadow-inner flex flex-col items-center">
                    <div class="absolute -top-[3px] left-6 bg-[#8a4a9e] border-x-[3px] border-b-[3px] border-[#703b96] text-white font-black text-sm px-6 py-1.5 rounded-b-xl">
                        그림 그리기
                    </div>

                    <div class="w-full bg-[#fff5fe] border-[3px] border-[#8a429b] rounded-2xl px-4 py-2 mt-3 mb-3 text-center">
                        <p class="text-[#42215c] font-black text-sm">주제: {{ round.previousContent }}</p>
                    </div>

                    <!-- 툴바 -->
                    <div class="flex items-center gap-3 mb-3">
                        <input type="color" v-model="brushColor" :disabled="round.hasSubmitted" class="w-9 h-9 rounded-lg border-2 border-[#703b96] cursor-pointer disabled:opacity-50" />
                        <input type="range" min="1" max="20" v-model="brushSize" :disabled="round.hasSubmitted" class="w-24" />
                        <button @click="undo" :disabled="round.hasSubmitted" class="bg-[#d3aade] border-2 border-[#703b96] text-[#42215c] font-black text-xs px-3 py-2 rounded-lg disabled:opacity-50">되돌리기</button>
                        <button @click="clearCanvas" :disabled="round.hasSubmitted" class="bg-[#d3aade] border-2 border-[#703b96] text-[#42215c] font-black text-xs px-3 py-2 rounded-lg disabled:opacity-50">전체 지우기</button>
                    </div>

                    <canvas
                        ref="canvasRef"
                        width="600" height="320"
                        class="bg-white rounded-xl border-[3px] border-[#8a429b] touch-none"
                        @mousedown="startDraw" @mousemove="draw" @mouseup="endDraw" @mouseleave="endDraw"
                        @touchstart.prevent="startDrawTouch" @touchmove.prevent="drawTouch" @touchend.prevent="endDraw"
                    ></canvas>
                </div>
            </div>

            <!-- 하단: 완료 현황 + 타이머 + 완료 버튼 -->
            <div v-if="!round.finished" class="flex flex-wrap gap-4 w-full">
                <div class="flex-1 min-w-[150px] bg-[#bfa2db]/40 border-[3px] border-[#703b96] text-[#42215c] font-black text-base py-3 rounded-xl flex items-center justify-center shadow-inner">
                    완료된 플레이어: {{ round.submittedCount }} / {{ round.totalPlayers }}
                </div>
                <div class="flex-1 min-w-[150px] bg-[#bfa2db]/40 border-[3px] border-[#703b96] text-[#42215c] font-black text-base py-3 rounded-xl flex items-center justify-center shadow-inner">
                    남은 시간: {{ timeLeft }} / {{ timeLimit }}
                </div>
                <button
                    @click="handleSubmit"
                    :disabled="round.hasSubmitted"
                    class="flex-1 min-w-[130px] bg-[#d3aade] hover:bg-[#c39ac7] border-[3px] border-[#703b96] text-[#42215c] font-black text-base py-3 rounded-xl transition-transform active:scale-95 disabled:opacity-50"
                >
                    {{ round.hasSubmitted ? '제출 완료!' : '완료' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

const chains = ref([]);
const activeChain = ref(0);

const fetchResults = async () => {
    const res = await window.axios.get(`/game/${props.lobbyCode}/results`);
    chains.value = res.data.chains;
};

const returnToLobby = async () => {
    await window.axios.post(`/game/${props.lobbyCode}/return-to-lobby`);
    router.visit(`/lobby/${props.lobbyCode}?isHost=true`);
};

const props = defineProps({
    lobbyCode:   { type: String, required: true },
    mySessionId: { type: String, default: '' },
    nickname:    { type: String, default: '플레이어' },
    avatar:      { type: String, default: '/images/profile.png' },
    isHost:      { type: Boolean, default: false },
    mode:        { type: String, default: 'normal' },
    timeLimit:   { type: Number, default: 40 },
    players:     { type: Array, default: () => [] },
    round:       { type: Object, required: true },
});

const modeText = computed(() => props.mode === 'normal' ? '일반' : props.mode);

// round 데이터를 반응형으로 다루기 위해 로컬 상태로 복사
const round = ref({ ...props.round });
const submittedSessions = ref(new Set(props.round.submittedSessions || []));
const textInput = ref('');
const timeLeft = ref(props.timeLimit);

// ── 그림 그리기 관련 ──
const canvasRef = ref(null);
const brushColor = ref('#000000');
const brushSize = ref(4);
let ctx = null;
let isDrawing = false;
let strokes = []; // undo용 스냅샷 저장

const initCanvas = () => {
    if (!canvasRef.value) return;
    ctx = canvasRef.value.getContext('2d');
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvasRef.value.width, canvasRef.value.height);
    strokes = [];
};

const saveSnapshot = () => {
    if (!ctx || !canvasRef.value) return;
    strokes.push(ctx.getImageData(0, 0, canvasRef.value.width, canvasRef.value.height));
    if (strokes.length > 30) strokes.shift();
};

const getPos = (e) => {
    const rect = canvasRef.value.getBoundingClientRect();
    const scaleX = canvasRef.value.width / rect.width;
    const scaleY = canvasRef.value.height / rect.height;
    return { x: (e.clientX - rect.left) * scaleX, y: (e.clientY - rect.top) * scaleY };
};

const startDraw = (e) => {
    if (round.value.hasSubmitted) return;
    saveSnapshot();
    isDrawing = true;
    const { x, y } = getPos(e);
    ctx.beginPath();
    ctx.moveTo(x, y);
};
const draw = (e) => {
    if (!isDrawing) return;
    const { x, y } = getPos(e);
    ctx.strokeStyle = brushColor.value;
    ctx.lineWidth = brushSize.value;
    ctx.lineTo(x, y);
    ctx.stroke();
};
const endDraw = () => { isDrawing = false; };

const startDrawTouch = (e) => startDraw(e.touches[0]);
const drawTouch = (e) => draw(e.touches[0]);

const undo = () => {
    if (strokes.length === 0) return;
    const last = strokes.pop();
    ctx.putImageData(last, 0, 0);
};
const clearCanvas = () => {
    saveSnapshot();
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvasRef.value.width, canvasRef.value.height);
};

// ── 타이머 ──
let timerInterval = null;
const resetTimer = () => {
    clearInterval(timerInterval);
    timeLeft.value = props.timeLimit;
    timerInterval = setInterval(() => {
        if (timeLeft.value > 0) timeLeft.value--;
        if (timeLeft.value === 0 && !round.value.hasSubmitted) handleSubmit();
    }, 1000);
};

// ── 라운드 데이터 새로고침 (다른 사람이 다 제출해서 라운드가 바뀌었을 때) ──
const refreshRound = async () => {
    const res = await window.axios.get(`/game/${props.lobbyCode}/round`);
    round.value = res.data;
    submittedSessions.value = new Set(res.data.submittedSessions || []);
    textInput.value = '';
    resetTimer();
    if (res.data.type === 'drawing') {
        await nextTick();
        initCanvas();
    }
    if (res.data.finished) {
        await fetchResults(); // 게임 종료 후 결과를 가져옴(GameController의 109번째 줄)
    }
};

// ── 제출 ──
const handleSubmit = async () => {
    if (round.value.hasSubmitted) return;

    let content = '';
    if (round.value.type === 'text') {
        content = textInput.value.trim();
    } else {
        content = canvasRef.value.toDataURL('image/png');
    }

    round.value.hasSubmitted = true; // 낙관적 업데이트

    try {
        const res = await window.axios.post(`/game/${props.lobbyCode}/submit`, {
            round: round.value.round,
            type: round.value.type,
            content,
        });
        round.value.submittedCount = res.data.submitted;
        submittedSessions.value.add(props.mySessionId);
    } catch (e) {
        round.value.hasSubmitted = false;
        console.error('제출 실패:', e);
    }
};

let echoChannel = null;

onMounted(() => {
    resetTimer();
    if (round.value.type === 'drawing') {
        nextTick(() => initCanvas());
    }

    echoChannel = window.Echo.channel(`game.${props.lobbyCode}`)
        .listen('.topic.submitted', (e) => {
            // 실시간 제출 카운트만 우선 반영 (같은 라운드일 때)
            round.value.submittedCount = e.submitted_count;
            if (e.session_id) submittedSessions.value.add(e.session_id);
        })
        .listen('.round.advanced', () => {
            refreshRound();
        });
        .listen('.game.ended', () => {
            router.visit(`/lobby/${props.lobbyCode}`);
        })
});

onUnmounted(() => {
    clearInterval(timerInterval);
    window.Echo.leaveChannel(`game.${props.lobbyCode}`);
});
</script>

<style scoped>
.custom-scroll::-webkit-scrollbar { width: 6px; }
.custom-scroll::-webkit-scrollbar-track { background: transparent; }
.custom-scroll::-webkit-scrollbar-thumb { background-color: #865bc6; border-radius: 9999px; }
</style>