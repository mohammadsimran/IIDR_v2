/*
Copyright (c) 2010, Yahoo! Inc. All rights reserved.
Code licensed under the BSD License:
http://developer.yahoo.com/yui/license.html
version: 3.2.0
build: 2676
*/
YUI.add('yui-later', function(Y) {

/**
 * Provides a setTimeout/setInterval wrapper
 * @module yui
 * @submodule yui-later
 */
(function() {
    var L = Y.Lang,

    /**
     * Executes the supplied function in the context of the supplied 
     * object 'when' milliseconds later.  Executes the function a 
     * single time unless periodic is set to true.
     * @method later
     * @for YUI
     * @param when {int} the number of milliseconds to wait until the fn 
     * is executed.
     * @param o the context object.
     * @param fn {Function|String} the function to execute or the name of 
     * the method in the 'o' object to execute.
     * @param data [Array] data that is provided to the function.  This accepts
     * either a single item or an array.  If an array is provided, the
     * function is executed with one parameter for each array item.  If
     * you need to pass a single array parameter, it needs to be wrapped in
     * an array [myarray].
     * @param periodic {boolean} if true, executes continuously at supplied 
     * interval until canceled.
     * @return {object} a timer object. Call the cancel() method on this object to 
     * stop the timer.
     */
    later = function(when, o, fn, data, periodic) {
        when = when || 0; 

        var m = fn, f, id;

        if (o && L.isString(fn)) {
            m = o[fn];
        }

        f = !L.isUndefined(data) ? function() {
            m.apply(o, Y.Array(data));
        } : function() {
            m.call(o);
        };

        id = (periodic) ? setInterval(f, when) : setTimeout(f, when);

        return {
            id: id,
            interval: periodic,
            cancel: function() {
                if (this.interval) {
                    clearInterval(id);
                } else {
                    clearTimeout(id);
                }
            }
        };
    };

    Y.later = later;
    L.later = later;

})();


}, '3.2.0' ,{requires:['yui-base']});
