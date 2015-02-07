/////////////////////////////////////////////////////////////////////////////
// PuzzleMaker Module
// (c) 2014 by Das PeTe
//////////////////////////////////////////////////////////////////////////




if (typeof console === 'undefined' || typeof console.log === 'undefined') {
    console = {
        log: function(o){},
        dir: function(o){}
    };
}

log = function(o){ console.log(o); };
dir = function(o){ console.dir(o); };





// UMD PATTERN
(function (root, factory) {
    if (typeof define === "function" && define.amd) { // AMD ready
        define([
            'jquery', 
            'backbone'
        ], factory);
    }else if(typeof exports === 'object'){ // nodejs
        module.exports=factory(
            require('jquery'), 
            require('underscore'
        ));
    }else{ // NO-AMD
        root.PuzzleMaker=factory(
            root.$, 
            root._
        );
    }
}(this, function (
    $,
    _
){
// END UMD PATTERN
    // PuzzleMaker Constructor
    function PuzzleMaker(settings){
        
        // default settings
        var defaults={
            generator: 'php/PuzzleMaker.php',
            tilesX: 3,
            tilesY: 3
        };
        
        // PuzzleMaker MainModule
        var puzzle_maker={
            
            settings: {},

            environment: {},

            DOM: {},
           
            init: function(settings, defaults){
                _.bindAll.apply(_, [this].concat(_.functions(this)));

                $.extend(this.settings, defaults, settings);

                this.getEnvironment();
                this.setup();
            },

            setup: function(){
                
            },

            getTiles: function(callback){
                $.ajax({
                    url: this.settings.generator,
                    cache: false,
                    type: 'post',
                    data: {
                        tilesX: this.settings.tilesX,
                        tilesY: this.settings.tilesY
                    },
                    success: function(data){
                        callback(JSON.parse(data));
                    }
                });
            },

            getEnvironment: function(){
                this.environment=this.getBrowser();
                this.environment.prefix=this.getPrefix();
            },

            getPrefix: function(){
                var styles = window.getComputedStyle(document.documentElement, ''),
                    pre = (Array.prototype.slice
                                .call(styles)
                                .join('') 
                                .match(/-(moz|webkit|ms)-/) || (styles.OLink === '' && ['', 'o'])
                    )[1],
                    dom = ('WebKit|Moz|MS|O').match(new RegExp('(' + pre + ')', 'i'))[1];
                
                return {
                    dom: dom,
                    lowercase: pre,
                    css: '-' + pre + '-',
                    js: pre[0].toUpperCase() + pre.substr(1)
                };
            },

            getBrowser: function(){
                var ua= navigator.userAgent, tem, 
                M= ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];
                if(/trident/i.test(M[1])){
                    tem=  /\brv[ :]+(\d+)/g.exec(ua) || [];
                    return{
                        browser: 'IE',
                        version: tem[1] || ''
                    };
                }
                if(M[1]=== 'Chrome'){
                    tem= ua.match(/\bOPR\/(\d+)/);
                    if(tem!= null) 
                        return {
                            browser: 'Opera',
                            version: tem[1]
                        };
                }
                M= M[2]? [M[1], M[2]]: [navigator.appName, navigator.appVersion, '-?'];
                if((tem= ua.match(/version\/(\d+)/i))!= null) M.splice(1, 1, tem[1]);

                return {
                    browser: M[0],
                    version: M[1]
                };
            }

        };
        // END PuzzleMaker MainModule
        
        // Initialize PuzzleMaker
        if(typeof settings === "undefined"){
            settings=defaults;
        }

        puzzle_maker.init(settings, defaults);
        
        return puzzle_maker;
    }

    return PuzzleMaker;
}));