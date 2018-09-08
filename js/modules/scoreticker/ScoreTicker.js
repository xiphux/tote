define(['axios', './ScoreTickerGame'], function(axios, ScoreTickerGame) {
    return {
        template: `
        <div :class="['rounded-top', 'subShadow', 'tickerToggleDiv', 'subSection', { 'rounded-bottom': hidden }]">
            <a
                :class="{ tickerToggleLink: true, tickerClosed: hidden, tickerOpen: !hidden }"
                href="#"
                @click="hidden = !hidden"
                >Score ticker{{ hidden ? '...' : '' }}</a>
            <div v-if="!hidden">
                <div class="tickerTitle">
                    <span v-if="weekString">{{ weekString }}</span>
                    <span v-else>Loading...</span>
                    <img src="images/scoreticker-loader.gif" style="margin-left: 10px; display: inline-block" v-if="loading" />
                </div>
                <div class="tickerContainerDiv" v-if="tickerData && tickerData.gameScores">
                    <table class="tickerGameTable">
                        <tr>
                            <ScoreTickerGame v-for="game in tickerData.gameScores" :key="game.gameSchedule.gameKey" :game="game" />
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        `,
        components: {
            ScoreTickerGame,
        },
        data: {
            hidden: false,
            tickerData: null,
            loading: false,
            timer: null,
        },
        watch: {
            hidden: function(newHidden, oldHidden) {
                if (newHidden && !oldHidden) {
                    if (this.timer) {
                        clearTimeout(this.timer);
                    }
                }
                if (!newHidden && oldHidden) {
                    this.update();
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
        },
        created: function() {
            this.update();
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
                        }, 15000);
                    }
                });
            },
        },
    };
});
