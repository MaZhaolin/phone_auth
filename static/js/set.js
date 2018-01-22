var Pagination = function(opt){
    var opt = opt || {};
    this.page = document.querySelector(opt.selector || '.pagination');
    this.nextElement = this.page.querySelector(opt.next || '.next');
    this.prevElement = this.page.querySelector(opt.prev || '.prev');
    this.goButton = this.page.querySelector(opt.to || '.to');
    this.inputPage = this.page.querySelector(opt.pageInput || 'input[name="page"]');
    this.currentPage = opt.currentPage || 1;
    this.pageTotal = opt.pageTotal || 1;
    this.length = opt.length || 10;
    this.changeHandle = opt.changeHandle || function(){};
    this._bindEventlistener();
}
Pagination.prototype = {
    constructor: Pagination,
    _createPage: function(page){
        var item = document.createElement('li'),
            a = document.createElement('a');
        a.innerText = page;
        item.classList.add('page-' + page);
        item.appendChild(a);
        this.page.insertBefore(item, this.nextElement);
        this.items.push(item);
        typeof page === 'number' && item.addEventListener('click', function() {
            this.to(Number(item.innerText));
        }.bind(this));
    },
    _createPages: function(){
        this.items = [];
        if (this.pageTotal > this.length) {
            if( this.currentPage > 5 ){
                this._createPage(1);
                this.currentPage > 6 && this._createPage('...');
            }
            for(var i = this.currentPage - 4; i <= this.currentPage + 5; i++){
                i > 0 && i < this.pageTotal && this._createPage(i);
            }
            if( this.currentPage <  this.pageTotal - 5){
                this._createPage('...');
            }
            this._createPage(this.pageTotal);
        } else {
                for(var i = 1; i <= this.pageTotal; i++){
                this._createPage(i);
            }
        }
    },
    _bindEventlistener: function(){
        this._createPages();
        this.nextElement.addEventListener('click', this.next.bind(this));
        this.prevElement.addEventListener('click', this.prev.bind(this));
        this.goButton && this.goButton.addEventListener('click', function(){
            this.to(Number(this.inputPage.value));
        }.bind(this));
        this.inputPage && this.inputPage.addEventListener('keyup', function(e){
            e = e || window.event;
            e.keyCode === 13 && this.to(Number(this.inputPage.value));
        }.bind(this))

        this._updateView(true);
    },
    next: function() {
        this.currentPage < this.pageTotal && this.currentPage++;
        this._updateView();
        return this;
    },
    prev: function() {
        this.currentPage > 1 && this.currentPage--;
        this._updateView();
        return this;
    },
    to: function(page) {
        if(page != this.currentPage && page <= this.pageTotal && page > 0 ){
            this.currentPage = page;
            this._updateView();
        }
        return this;
    },
    reset: function(pageTotal) {
        pageTotal && (this.pageTotal = pageTotal);
        this.currentPage = 1; 
        this._updateView(true);
    },
    _removeDoms: function(){
        this.items.forEach(function(item){
            item.remove();
        })
    },
    _updateView: function(notChange) {
        !notChange && this.changeHandle(this.currentPage);
        this._removeDoms();
        this._createPages();
        this.items.forEach(function(item){
            item.classList.remove('active');
        })
        this.page.style.display  = this.pageTotal <= 1 ? 'none' : 'inline-block';
        this.currentPage === 1 ? this.prevElement.classList.add('disabled') : this.prevElement.classList.remove('disabled')
        this.currentPage === this.pageTotal ? this.nextElement.classList.add('disabled') : this.nextElement.classList.remove('disabled')
        this.page.getElementsByClassName('page-' + this.currentPage)[0].classList.add('active');
    }
}

var helper = function(config) {
    this.config = config;
    this.selectedAmount = 5000;
    this.alipayToken = '';
    this.wechatToken = '';
    this.checkPayTimer;
}
helper.prototype = {
    constructor: helper,
    init: function() {
        this.initSetting();
        this.initTabs();
        this.initSelectAmount();
        this.setAliayUrl();
        // this.getOrdersData();
        this.getSendRecord();
        this.initPayEvents();
    },
    initSetting: function() {
        $('#picker').colpick({
            layout: 'hex',
            submit: 0,
            colorScheme: 'dark',
            onChange: function (hsb, hex, rgb, el, bySetColor) {
                $(el).css('border-color', '#' + hex);
                if (!bySetColor) $(el).val(hex);
            }
        }).keyup(function () {
            $(this).colpickSetColor(this.value);
        });
    },
    initTabs: function() {
        var self = this;
        var tabs = $('.tab .v-btn'),
        tabPanels = $('.tab-panel');
        tabs.click(function () {
            tabs.removeClass('active');
            tabPanels.removeClass('active');
            $(this).addClass('active');
            var index = tabs.index(this);
            $(tabPanels[index]).addClass('active');
            index == 0 && self.getOrdersData();
            index == 1 && self.getSendRecord();
        })
    },
    initSelectAmount: function() {
        var prices = [
            { price: 3.5, num: 5000 },
            { price: 3.2, num: 20000 },
            { price: 2.8, num: 50000 },
            { price: 2.5, num: 200000 },
            { price: 2.0, num: 500000 },
        ];
        var priceNodes = $('.recharge .prices span');
        var self = this;
        priceNodes.click(function (e) {
            var index = priceNodes.index(this);
            priceNodes.removeClass('active');
            $(this).addClass('active');
            self.selectedAmount = prices[index].num;
            $('input[name=pay]:checked').val() && self.setAliayUrl();
            $('.recharge .v-money .price-total').text('￥' + (prices[index].price * prices[index].num / 100).toFixed(2));
            $('.recharge .v-money .price').text(prices[index].price);
        })
    },
    setAliayUrl: function() {
        var self = this;
        var url = this.config.site_url + '/plugin.php?id=phone_auth&action=smspay&type=alipay&amount=' + self.selectedAmount;
        $.get(url, function(data) {
            self.alipayToken = data.token;
            $('.online-pay-btn').attr('href', data.url);
        }, 'json')
    },
    initPayEvents: function() {
        var timer = null;
        var self = this;
        $('.online-pay-btn').click(function() {
            if (self.config.params.vid.length != 24 || self.config.params.key.length != 32){
                $('.vaptcha-dz-tip').show();
                timer && clearTimeout(timer);
                timer = setTimeout(() => {
                    $('.vaptcha-dz-tip').hide();
                }, 1000);
                return false;
            } else {
                if ($('input[name=pay]:checked').val() === 'wechat') {
                    var url = self.config.site_url + '/plugin.php?id=phone_auth&action=smspay&type=wechat&amount=' + self.selectedAmount;
                    $.get(url, function(data) {
                        self.wechatToken = data.token;
                        $('.wechat-pay img').attr('src', 'data:image/png;base64, ' + data.data);
                        $('.wechat-pay').show();
                        self.checkPayState(self.wechatToken);
                    }, 'json')
                    return false;
                } else {
                    self.checkPayState(self.alipayToken);
                    $('.alipay-pop').show();
                }
            }
        })
        $('.alipay-pop .finish-pay').click(function() {
            self.checkPayState(self.alipayToken);
        });
    },
    getOrdersData: function() {
        var self = this;
        $.get(this.config.site_url + '/plugin.php?id=phone_auth&action=smsdata&page=0', function(data) {
            if (data.code !== 200) {
                console.error('get data error');
                return ;
            }
            data = data.data;
            console.log(data);
            $('.surplus-count .count').html(data.amount);
            $('.surplus-day .day').html(data.expecttime);
            var tr = '';
            for(var i in data.orders) {
                var order = data.orders[i];
                tr += '<tr><td>' + order.amount + '</td><td>' + order.payment + '</td><td>'
                + self.config.lang[order.paytype] + '</td><td>' + order.orderid + '</td><td>' + (new Date(order.createtime)).toLocaleString() + '</td></tr>'
            }
            tr && $('.recharge .record tbody').html(tr);
        }, 'json')
    },
    getSendRecord: function() {
        var self = this;
        $.get(this.config.site_url + '/plugin.php?id=phone_auth&action=smsdata&type=send&page=0', function(data) {
            if (data.code !== 200) {
                console.error('get data error');
                return ;
            }
            data = data.data;
            self.initStatistics(data.statistics);
            var tr = '';
            for(var i in data.records) {
                var record = data.records[i];
                tr += '<tr><td>' + record.phone + '</td><td>' + record.content + '</td><td>'
                + record.consume + '</td><td>' + record.type + '</td><td>'  + (record.statucode == '100' ? '<i class="iconfont success">&#xe625;</i>' : '<i class="iconfont error">&#xe6b8;</i>') + '</td><td>'  + (new Date(record.createtime)).toLocaleString() + '</td></tr>'
            }
            tr && $('.log tbody').html(tr);
            var page = new Pagination({
                pageTotal: 3
            })
        }, 'json')
    },
    showState: function(type){
        $('.vaptcha-dz-tip').hide();
        $('.pay-' + type).show();
        setTimeout(function() {
            $('.pay-' + type).hide();            
        }, 5000)
    },
    checkPayState: function(token) {
        var self = this;
        $.get(this.config.site_url + '/plugin.php?id=phone_auth&action=paycheck&token=' + token, function(data) {
            var code = data.data;
            self.checkPayTimer && clearTimeout(self.checkPayTimer);
            if (code == "0") {
                //wait pay
                self.checkPayTimer = setTimeout(function () {
                    self.checkPayState(token);
                }, 1000);
            } else if (code == '2') {
                // pay success
                self.getOrdersData();
                $('.vaptcha-dz-pop').hide();
                self.showState('success');
            } else {
                self.showState('error');
                //pay error
            }
        }, 'json')
    },
    initStatistics: function(data) {
        var myChart = echarts.init(document.getElementById('echart'));
        var dates = [];
        var counts = [];
        for(var i = 0; i < data.length; i++) {
            dates.push(data[i].date);
            counts.push(data[i].count);
        }
        var option = {
            color: ['#0088ff'],
            grid: {
                left: 50,
                top: 20
            },
            tooltip: {},
            legend: {
                data:['销量']
            },
            xAxis: {
                type: 'category',
                boundaryGap: false,
                data: dates
            },
            yAxis: {
                type: 'value',
            },
            series: [{
                name: '发送量',
                type: 'line',
                data: counts
            }]
        };
        myChart.setOption(option);        
    }
}