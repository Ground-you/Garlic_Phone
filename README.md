# 🧄 Garlic Phone (갈틱폰)
## 🎮 게임 규칙 (Game Rule)
첫 번째 플레이어가 주제를 정한 뒤, 두 번째 플레이어가 그 주제를 바탕으로 그림을 그리고, 세 번째 플레이어가 그 그림을 보고 주제를 추리하고 작성한 뒤, 네 번때 플레이어가 세 번째 플레이어가 추리한 주제를 보고 그림을 그리는 방식을 취한다.

---

## 🛠️ 기술 스택 (Tech Stack)

### 🎨 Frontend
* **Vue 3 (`<script setup>`)**: 컴포넌트 기반 반응형 프론트엔드 프레임워크
* **HTML5 Canvas API**: 인게임 실시간 스케치북 패드 및 드로잉 로직 구현
* **Tailwind CSS**: GPU 가속 애니메이션 및 유연한 UI 레이아웃 스타일링
* **Laravel Echo**: 백엔드 웹소켓 신호를 실시간으로 수신하는 클라이언트 라이브러리

### ⚙️ Backend & Infrastructure
* **Laravel 13 (PHP)**: 세션 제어, 대기방 생성, 게임 라운드 로직을 총괄하는 메인 백엔드
* **Inertia.js**: API 설계 없이 라라벨과 Vue 3 사이의 데이터와 라우팅을 긴밀하게 연결하는 다리 역할
* **Laravel Reverb**: 라라벨 13 내장 고성능 **웹소켓(WebSocket) 서버**로, 실시간 채팅 및 그림 데이터 브로드캐스팅 담당
* **PostgreSQL**: 방 정보, 참가자 세션, 라운드별 제시어 및 그림 경로 저장

---

## 🚀 셋업 방법 (Setup Guide)

새로운 개발 환경에서 프로젝트를 복제하고 로컬 서버를 구동하기 위한 순서입니다.

### 1. 저장소 복제 및 폴더 이동
~~~bash
git clone <본인의-깃허브-레포지토리-주소>.git
cd Garlic_Phone
~~~
### 2. 패키지 의존성 설치
~~~bash
# 라라벨 백엔드 의존성 설치
composer install

# Vue 및 프론트엔드 패키지 설치
npm install
~~~
### 3. 환경 설정 (.env) 세팅
~~~
# 환경 설정 샘플 파일 복사
cp .env.example .env

# 라라벨 고유 애플리케이션 키 생성
php artisan key:generate
~~~
### 4. 데이터베이스 마이그레이션
~~~
# 설계된 데이터베이스 테이블(rooms, participants 등) 생성
php artisan migrate
~~~
### 5. 로컬 개발 서버 구동 (각각의 터미널 탭에서 실행)
~~~
# [Terminal 1] 라라벨 애플리케이션 로컬 서버 실행
php artisan serve

# [Terminal 2] 실시간 웹소켓 서버(Laravel Reverb) 실행
php artisan reverb:start

# [Terminal 3] 프론트엔드 Vite 컴파일러 및 핫 리로드 실행
npm run dev
~~~

---

## 🌿 브랜치 전략 (Branch Strategy)

**Git Flow** 패러다임을 기반으로 한 브랜치 전략을 채택하고 있습니다.

### 📌 브랜치 구조 및 규칙

* **`main` (Production)**
  * 실서버에 배포되는 가장 안정적인 최상위 브랜치입니다. 직접적인 커밋 및 푸시는 금지됩니다.
* **`develop` (Development)**
  * 다음 출시 버전을 위해 개발된 기능들이 통합되는 기준 브랜치입니다. 모든 기능 브랜치는 이곳에서 출발하고 이곳으로 합쳐집니다.
* **`feature/*` (Feature Branches)**
  * 새로운 기능이나 UI 화면을 개발하는 독립된 브랜치입니다. 다른 기능의 간섭 없이 안전하게 개발을 진행합니다.
  * **Naming Rule**: `feature/기능명` (예: `feature/home`, `feature/lobbySetting`)
  * 기능 개발 완료 후 `develop` 브랜치를 대상으로 Pull Request(PR)를 보내 코드 리뷰를 거쳐 머지(Merge)합니다.

### 🔄 작업 프로세스 (Work Flow)

1. `develop` 브랜치에서 최신 코드를 pull 받아 로컬 환경을 업데이트합니다.
2. 새 기능을 구현하기 위한 브랜치를 파고 이동합니다. (`feature/기능명`)
3. 독립된 공간에서 기능을 구현하고 의미 있는 단위로 커밋을 남깁니다.
4. 개발이 끝나면 `develop`의 최신 변경 내용을 내 기능 브랜치에 merge하여 충돌(Conflict)을 먼저 해결합니다.
5. 깃허브 웹(GitHub)에서 `develop` 브랜치를 대상으로 Pull Request를 생성하여 코드 검토 후 최종 통합합니다.