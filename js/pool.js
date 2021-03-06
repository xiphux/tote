define([
    'jquery',
    'cs!modules/autoselectnav',
    'cs!modules/poolpopup',
    'cs!modules/feedtip',
    'cs!modules/schedulepopup',
    'cs!modules/collapsiblesection',
    'modules/scoreticker/ScoreTicker',
    'Vue',
    'cs!modules/titletips',
    'common',
], function(
    $,
    autoselectnav,
    poolpopup,
    feedtip,
    schedulepopup,
    collapsiblesection,
    ScoreTicker,
    Vue
) {
    autoselectnav('#poolNameSelect', '#poolNameSubmit');

    poolpopup('a#lnkHistory', 'history', 'Pool History');
    poolpopup('a#lnkRules', 'rules', 'Pool Rules');
    feedtip('a.feedTip');
    schedulepopup('.scheduleLink');

    collapsiblesection('#linksSection', 'ToteLinksExpanded');

    scoreTickerVue = new Vue(ScoreTicker);
    scoreTickerVue.$mount('#scoreTicker');
});
