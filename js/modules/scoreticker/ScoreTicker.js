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
                        <transition name="fade">
                            <img src="images/scoreticker-loader.gif" style="margin-left: 10px; display: inline-block" v-if="loading" />
                        </transition>
                    </div>
                    <div class="tickerContainerDiv" v-if="tickerData && tickerData.gameScores">
                        <table class="tickerGameTable" ref="tickerTable">
                            <tr>
                                <ScoreTickerGame v-for="game in tickerData.gameScores" :key="game.gameSchedule.gameKey" :game="game" />
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
        data: {
            hidden: !!localStorage.getItem('toteScoretickerHidden'),
            tickerData: null,
            loading: false,
            timer: null,
            contentWidth: null,
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
                    ' week ' +
                    this.tickerData.week
                );
            },
            fastUpdate: function() {
                if (!(this.tickerData && this.tickerData.gameScores)) {
                    return false;
                }
                var now = Date.now();
                for (var i = 0; i < this.tickerData.gameScores.length; i++) {
                    var game = this.tickerData.gameScores[i];
                    if (game.score) {
                        switch (game.score.phase) {
                            case 'INGAME':
                            case 'HALFTIME':
                                return true;
                        }
                    }
                    var left = game.gameSchedule.isoTime - now;
                    if (left < 15 * 60 * 1000 && left >= 0) {
                        return true;
                    }
                }
                return false;
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
            update: function() {
                this.loading = true;
                data = axios.get('scoreticker.php').then((ticker) => {
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
                });
            },
        },
    };
});
