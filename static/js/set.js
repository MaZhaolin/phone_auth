$(document).ready(function () {
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
    var tabs = $('.tab .v-btn'),
        tabPanels = $('.tab-panel');
    tabs.click(function () {
        tabs.removeClass('active');
        tabPanels.removeClass('active');
        $(this).addClass('active');
        $(tabPanels[tabs.index(this)]).addClass('active');
    })
    var prices = [
        { price: 3.5, num: 5000 },
        { price: 3.2, num: 20000 },
        { price: 2.8, num: 50000 },
        { price: 2.5, num: 200000 },
        { price: 2.0, num: 500000 },
    ];
    var selectedAmount = prices[0].num;
    var priceNodes = $('.recharge .prices span');
    var setAliayUrl = function() {
        var url = config.site_url + '/plugin.php?id=phone_auth&action=smspay&type=alipay&amount=' + selectedAmount;
        $.get(url, function(data) {
            $('.online-pay-btn').attr('href', data);
        })
    }
    setAliayUrl();
    priceNodes.click(function (e) {
        var index = priceNodes.index(this);
        priceNodes.removeClass('active');
        $(this).addClass('active');
        selectedPrice = prices[index].num;
        $('input[name=pay]:checked').val() && setAliayUrl();
        $('.recharge .v-money .price-total').text('￥' + (prices[index].price * prices[index].num / 100).toFixed(2));
        $('.recharge .v-money .price').text(prices[index].price);
    })

    var timer = null;
    $('.online-pay-btn').click(function() {
        if (config.params.vid.length != 24 || config.params.key.length != 32){
            $('.vaptcha-dz-tip').show();
            timer && clearTimeout(timer);
            timer = setTimeout(() => {
                $('.vaptcha-dz-tip').hide();
            }, 1000);
            return false;
        } else {
            if ($('input[name=pay]:checked').val() === 'wechat') {
                var url = config.site_url + '/plugin.php?id=phone_auth&action=smspay&type=wechat&amount=' + selectedAmount;
                $.get(url, function(data) {
                    console.log(data);
                })
                return false;
            }
        }
    })

    $.get(config.site_url + '/plugin.php?id=phone_auth&action=smsdata', function(data) {
console.log(data);
        // for(var i in list) {
        //     var data = list[i];
        //     var tr = '<tr><td>' + data.Amount + '</td><td>' + data.Discount + '</td><td>'
        //     + (data.Payment == 'alipay' ? '支付宝' : '微信') + '</td><td>786934579823842930589230759</td><td>' + (new Date(data.TimeStamp)).toLocaleDateString() + '</td></tr>'
        // }
    })

    var page = new Pagination({
        pageTotal: 100
    })

    var myChart = echarts.init(document.getElementById('echart'));

    function randomData() {
        now = new Date(+now + oneDay);
        value = value + Math.random() * 21 - 10;
        return [now.getFullYear(), now.getMonth() + 1, now.getDate()].join('/')
    }
    
    var data = [];
    var now = +new Date(2018, 1, 1);
    var oneDay = 24 * 3600 * 1000;
    var value = Math.random() * 1000;
    for (var i = 0; i < 7; i++) {
        data.push(randomData());
    }
    // 指定图表的配置项和数据
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
            data: data
        },
        yAxis: {
            type: 'value',
        },
        series: [{
            name: '发送量',
            type: 'line',
            data: [1,34,43,45,56,657,54]
        }]
    };

    // 使用刚指定的配置项和数据显示图表。
    myChart.setOption(option);
})