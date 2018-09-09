define([], function() {
    return {
        template: `
            <td class="tickerGameCell">
                <a target="_blank" :href="link">
                    <table :class="['tickerGameTile', statusClass, { tickerGameRedZone: game.score && game.score.redZone }]">
                        <tr :class="{ tickerTeamWinner: visitorWin }">
                            <td class="tickerGameTeam">
                                {{ game.gameSchedule.visitorTeamAbbr }}
                            </td>
                            <td class="tickerPossession">
                                <transition name="fade">
                                    <span v-if="visitorPossession">&bull;</span>
                                </transition>
                            </td>
                            <td class="tickerGameScore">
                                <transition name="fade">
                                    <span v-if="!gamePending">{{ game.score.visitorTeamScore.pointTotal }}</span>
                                </transition>
                            </td>
                        </tr>
                        <tr :class="{ tickerTeamWinner: homeWin }">
                            <td class="tickerGameTeam">
                                {{ game.gameSchedule.homeTeamAbbr }}
                            </td>
                            <td class="tickerPossession">
                                <transition name="fade">
                                    <span v-if="homePossession">&bull;</span>
                                </transition>
                            </td>
                            <td class="tickerGameScore">
                                <transition name="fade">
                                    <span v-if="!gamePending">{{ game.score.homeTeamScore.pointTotal }}</span>
                                </transition>
                            </td>
                        </tr>
                        <tr>
                            <td class="tickerGameStatus" colspan="3">{{ status }}</td>
                        </tr>
                    </table>
                </a>
            </td>
        `,
        props: {
            game: Object,
        },
        computed: {
            link: function() {
                if (!(this.game && this.game.gameSchedule)) {
                    return '#';
                }
                return (
                    'http://www.nfl.com/gamecenter/' +
                    this.game.gameSchedule.gameId +
                    '/' +
                    this.game.gameSchedule.season +
                    '/' +
                    this.game.gameSchedule.seasonType +
                    this.game.gameSchedule.week +
                    '/' +
                    this.game.gameSchedule.visitorNickname +
                    '@' +
                    this.game.gameSchedule.homeNickname
                );
            },
            status: function() {
                if (!(this.game && this.game.gameSchedule)) {
                    return '';
                }
                if (!this.game.score) {
                    return this.startFormatted;
                }
                switch (this.game.score.phase) {
                    case 'FINAL':
                        return 'Final';
                    case 'FINAL_OVERTIME':
                        return 'Final OT';
                    case 'HALFTIME':
                        return 'Halftime';
                    case 'SUSPENDED':
                        return 'Suspended';
                    case 'PREGAME':
                        return this.startFormatted;
                }
                return this.game.score.phase + ' ' + this.game.score.time;
            },
            statusClass: function() {
                if (!this.game) {
                    return '';
                }
                if (this.gamePending) {
                    return 'tickerGamePending';
                }
                if (this.gameFinished) {
                    return 'tickerGameFinished';
                }
                return 'tickerPlaying';
            },
            gameFinished: function() {
                return !!(
                    this.game &&
                    this.game.score &&
                    this.game.score.phase === 'FINAL'
                );
            },
            gamePending: function() {
                if (!(this.game && this.game.score)) {
                    return true;
                }
                if (this.game.score.phase === 'PREGAME') {
                    return true;
                }
                return false;
            },
            showPossession: function() {
                return (
                    this.game &&
                    this.game.score &&
                    !this.gameFinished &&
                    !this.gamePending
                );
            },
            visitorPossession: function() {
                if (!this.showPossession) {
                    return false;
                }
                return (
                    this.game.score.possessionTeamId ===
                    this.game.gameSchedule.visitorTeamId
                );
            },
            homePossession: function() {
                if (!this.showPossession) {
                    return false;
                }
                return (
                    this.game.score.possessionTeamId ===
                    this.game.gameSchedule.homeTeamId
                );
            },
            homeWin: function() {
                return !!(
                    this.gameFinished &&
                    this.game.score &&
                    this.game.score.homeTeamScore.pointTotal >
                        this.game.score.visitorTeamScore.pointTotal
                );
            },
            visitorWin: function() {
                return !!(
                    this.gameFinished &&
                    this.game.score &&
                    this.game.score.visitorTeamScore.pointTotal >
                        this.game.score.homeTeamScore.pointTotal
                );
            },
            start: function() {
                if (!(this.game && this.game.gameSchedule)) {
                    return null;
                }
                return new Date(this.game.gameSchedule.isoTime);
            },
            startFormatted: function() {
                if (!this.start) {
                    return '';
                }
                var day = this.start.getDay();
                var dayStrs = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                var hours = this.start.getHours();
                var mins = this.start.getMinutes();
                return (
                    dayStrs[day] +
                    ' ' +
                    (hours > 12 ? hours - 12 : hours) +
                    ':' +
                    (mins < 10 ? '0' : '') +
                    mins
                );
            },
        },
    };
});
