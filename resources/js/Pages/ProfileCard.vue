<template>
    <Transition name="fade">
        <div
            v-if="isOpen"
            class="fixed inset-0 bg-black/60 z-40"
            @click="emit('close')"
        ></div>
    </Transition>

    <Transition name="popup">
        <div
            v-if="isOpen"
            class="absolute left-[130px] top-[-33px] z-50 select-none"
            @click.stop
        >
            <div class="absolute -left-[15px] top-[54px] w-0 h-0
                border-t-[12px] border-t-transparent
                border-r-[16px] border-r-[#b35cb8]
                border-b-[12px] border-b-transparent z-10">
            </div>
            <div class="absolute -left-[9px] top-[54px] w-0 h-0
                border-t-[12px] border-t-transparent
                border-r-[16px] border-r-[#f6dff2]
                border-b-[12px] border-b-transparent z-20">
            </div>

            <div class="w-[460px] bg-[#f6dff2] border-[5px] border-[#b35cb8] rounded-[32px] p-6 pb-8 shadow-[0_12px_24px_rgba(0,0,0,0.28)] flex flex-col gap-5 relative z-20">

                <div class="flex items-center gap-3 mt-3">

                    <div class="relative shrink-0 flex flex-col items-center">
                        <div
                            @click="!isLoggedIn ? triggerFileInput() : null"
                            class="w-[88px] h-[88px] rounded-full bg-white overflow-hidden border-[4px] border-[#b35cb8] shadow-md"
                            :class="!isLoggedIn ? 'cursor-pointer group' : 'cursor-default'"
                        >
                            <img
                                :src="previewUrl"
                                class="w-full h-full object-cover"
                                style="transition: transform 150ms;"
                                :class="!isLoggedIn ? 'group-hover:scale-110' : ''"
                            />
                        </div>
                    </div>

                    <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="handleFileChange" />

                    <div class="flex items-center gap-2 flex-1 relative mt-4">
                        
                        <button
                            v-if="!isLoggedIn"
                            @click="triggerFileInput"
                            class="absolute -top-[25px] left-2 bg-[#b35cb8] text-white font-black text-[11px] px-3 py-[3px] rounded-full shadow-sm whitespace-nowrap z-30 active:bg-[#7d3a82] active:scale-95"
                            style="transition: background-color 60ms, transform 60ms;"
                        >
                            프로필 사진 변경
                        </button>

                        <div class="flex-1 bg-[#ca8fe2] border-[3px] border-[#b35cb8] rounded-full px-6 py-3 min-h-[52px] flex items-center shadow-inner">
                            <input
                                v-model="localNickname"
                                :disabled="!editingNickname || isLoggedIn"
                                ref="nicknameInput"
                                maxlength="12"
                                class="w-full bg-transparent text-white font-black text-lg text-center border-none outline-none focus:outline-none focus:border-none focus:ring-0 p-0 m-0 disabled:cursor-default"
                                placeholder="닉네임..."
                            />
                        </div>

                        <button
                            @click="toggleNicknameEdit"
                            :disabled="isLoggedIn"
                            class="w-12 h-12 rounded-2xl border-[4px] border-[#b35cb8] flex items-center justify-center shrink-0 shadow-md"
                            :class="isLoggedIn
                                ? 'bg-[#ca8fe2]/40 cursor-not-allowed opacity-50'
                                : 'bg-[#ca8fe2] cursor-pointer active:bg-[#9e6bb8] active:scale-95'"
                            style="transition: background-color 60ms, transform 60ms;"
                        >
                            <svg v-if="editingNickname" viewBox="0 0 24 24" class="w-6 h-6 fill-white">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                            </svg>
                            <svg v-else viewBox="0 0 24 24" class="w-5 h-5 fill-white">
                                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Z M20.71 5.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.84 1.83 3.75 3.75 1.84-1.83Z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="relative w-full mt-1">
                    <div class="absolute left-10 -top-[14px] w-0 h-0
                        border-l-[10px] border-l-transparent
                        border-r-[10px] border-r-transparent
                        border-b-[15px] border-b-[#b35cb8] z-10">
                    </div>
                    <div class="absolute left-[41px] -top-[8px] w-0 h-0
                        border-l-[9px] border-l-transparent
                        border-r-[9px] border-r-transparent
                        border-b-[13px] border-b-[#fff5fe] z-20">
                    </div>

                    <textarea
                        v-model="localStatusMessage"
                        rows="3"
                        maxlength="80"
                        placeholder="한 마디를 입력하세요..."
                        class="w-full bg-[#fff5fe] border-[4px] border-[#b35cb8] rounded-[24px] px-5 py-4 text-[#50216b] font-black text-sm resize-none focus:outline-none placeholder-purple-300 shadow-inner relative z-30"
                    ></textarea>
                </div>
            </div>

            <div class="absolute -bottom-[54px] left-0 w-full flex justify-center z-30">
                <button
                    @click="handleConfirm"
                    class="min-w-[160px] px-8 bg-[#ca8fe2] border-[4px] border-[#b35cb8]
                           text-white font-black text-lg py-2 rounded-full
                           shadow-[0_4px_0_#97479c]
                           active:translate-y-[3px] active:shadow-none
                           tracking-wider hover:bg-[#b57dcd]"
                    style="transition: background-color 60ms, transform 60ms, box-shadow 60ms;"
                >
                    {{ isModified ? '변경사항 저장' : '확인' }}
                </button>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, watch, computed, nextTick } from 'vue';

const props = defineProps({
    isOpen:        Boolean,
    nickname:      String,
    statusMessage: String,
    avatarUrl:     String,
    isLoggedIn:    Boolean,
});

// ✅ file-selected 추가: 실제 File 객체를 Home.vue로 전달
const emit = defineEmits([
    'close',
    'update:nickname',
    'update:statusMessage',
    'update:avatarUrl',
    'file-selected',
]);

const localNickname      = ref(props.nickname || '');
const localStatusMessage = ref(props.statusMessage || '');
const previewUrl         = ref(props.avatarUrl || '/images/profile.png');
const editingNickname    = ref(false);
const fileInput          = ref(null);
const nicknameInput      = ref(null);

const isModified = computed(() => {
    return localNickname.value.trim() !== (props.nickname || '').trim()
        || localStatusMessage.value    !== (props.statusMessage || '')
        || previewUrl.value            !== (props.avatarUrl || '/images/profile.png');
});

watch(() => props.isOpen, (val) => {
    if (val) {
        localNickname.value      = props.nickname || '';
        localStatusMessage.value = props.statusMessage || '';
        previewUrl.value         = props.avatarUrl || '/images/profile.png';
        editingNickname.value    = false;
    }
});

watch(() => props.avatarUrl, (val) => {
    previewUrl.value = val || '/images/profile.png';
});

const toggleNicknameEdit = async () => {
    if (props.isLoggedIn) return;
    editingNickname.value = !editingNickname.value;
    if (editingNickname.value) {
        await nextTick();
        nicknameInput.value?.focus();
    }
};

const triggerFileInput = () => {
    if (props.isLoggedIn) return;
    fileInput.value?.click();
};

// ✅ File 객체도 함께 emit
const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    previewUrl.value = URL.createObjectURL(file);
    emit('update:avatarUrl', previewUrl.value);
    emit('file-selected', file);
};

const handleConfirm = () => {
    editingNickname.value = false;
    emit('update:nickname',       localNickname.value.trim() || props.nickname);
    emit('update:statusMessage',  localStatusMessage.value);
    emit('update:avatarUrl',      previewUrl.value);
    emit('close');
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.popup-enter-active {
    transition: opacity 0.25s ease-out, transform 0.28s cubic-bezier(0.34, 1.56, 0.64, 1);
    transform-origin: left center;
}
.popup-leave-active {
    transition: opacity 0.12s ease-in, transform 0.1s ease-in;
    transform-origin: left center;
}
.popup-enter-from { opacity: 0; transform: scale(0.6) translateX(-20px); }
.popup-leave-to   { opacity: 0; transform: scale(0.88) translateX(-8px); }
</style>