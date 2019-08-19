define(['axios', './ScoreTickerGame'], function(axios, ScoreTickerGame) {
    return {
        template: `
        <div :class="['rounded-top', 'subShadow', 'tickerToggleDiv', 'subSection', { 'rounded-bottom': hidden }]" :style="{ width: width + 'px' }">
            <a
                :class="{ tickerToggleLink: true, tickerClosed: hidden, tickerOpen: !hidden }"
                href="#"
                @click="hidden = !hidden"
                >Score ticker{{ hidden ? '...' : '' }}</a>
            <transition name="fadeTickerHeight">
                <div v-if="!hidden">
                    <div class="tickerTitle">
                        <span v-if="weekString">{{ weekString }}</span>
                        <span v-else>Loading...</span>
                        <div class="tickerLoaderContainer">
                            <transition name="fade">
                                <img src="images/scoreticker-loader.gif" v-show="loading" />
                            </transition>
                        </div>
                    </div>
                    <div class="tickerContainerDiv" v-if="tickerData && sortedGames">
                        <table class="tickerGameTable" ref="tickerTable">
                            <tr>
                                <ScoreTickerGame v-for="game in sortedGames" :key="game.gameSchedule.gameKey" :game="game" />
                            </tr>
                        </table>
                    </div>
                </div>
            </transition>
        </div>
        `,
        components: {
            ScoreTickerGame,
        },
        data: function() {
            return {
                hidden: !!localStorage.getItem('toteScoretickerHidden'),
                tickerData: null,
                loading: false,
                timer: null,
                contentWidth: null,
            };
        },
        watch: {
            hidden: function(newHidden, oldHidden) {
                if (newHidden && !oldHidden) {
                    if (this.timer) {
                        clearTimeout(this.timer);
                    }
                    localStorage.setItem('toteScoretickerHidden', '1');
                }
                if (!newHidden && oldHidden) {
                    this.update();
                    localStorage.setItem('toteScoretickerHidden', '');
                }
            },
        },
        computed: {
            sortedGames: function() {
                if (!(this.tickerData && this.tickerData.gameScores)) {
                    return [];
                }
                return this.tickerData.gameScores.slice().sort((a, b) => {
                    if (a.gameSchedule.isoTime !== b.gameSchedule.isoTime) {
                        return a.gameSchedule.isoTime - b.gameSchedule.isoTime;
                    }
                    return a.gameSchedule.gameId - b.gameSchedule.gameId;
                });
            },
            weekString: function() {
                if (!this.tickerData) {
                    return null;
                }
                if (
                    !(
                        this.tickerData.season &&
                        this.tickerData.seasonType &&
                        this.tickerData.week
                    )
                ) {
                    return null;
                }
                return (
                    this.tickerData.season +
                    '-' +
                    (this.tickerData.season + 1) +
                    (this.tickerData.seasonType === 'PRE' ? ' preseason' : '') +
                    ' week ' +
                    this.tickerData.week
                );
            },
            fastUpdate: function() {
                if (!(this.tickerData && this.tickerData.gameScores)) {
                    return false;
                }
                return !!this.tickerData.gameScores.find(
                    (g) =>
                        g.score &&
                        g.score.phase !== 'FINAL' &&
                        g.score.phase !== 'FINAL_OVERTIME'
                );
            },
            updateInterval: function() {
                return this.fastUpdate ? 15 * 1000 : 15 * 60 * 1000;
            },
            width: function() {
                if (!this.contentWidth) {
                    return 650;
                }
                return this.contentWidth;
            },
        },
        created: function() {
            if (!this.hidden) {
                this.update();
            }
        },
        methods: {
            update: function(fallback) {
                this.loading = true;
                var url = fallback
                    ? 'scoreticker.php'
                    : 'https://feeds.nfl.com/feeds-rs/scores.json';
                data = axios
                    .get(url)
                    .then((ticker) => {
                        this.tickerData = ticker.data;
                        this.loading = false;
                        if (!this.hidden) {
                            this.timer = setTimeout(() => {
                                this.update();
                            }, this.updateInterval);
                        }
                        this.$nextTick(() => {
                            if (this.$refs.tickerTable) {
                                this.contentWidth = this.$refs.tickerTable.clientWidth;
                            }
                        });
                    })
                    .catch((err) => {
                        if (!fallback) {
                            this.update(true);
                        }
                    });
            },
        },
    };
});
