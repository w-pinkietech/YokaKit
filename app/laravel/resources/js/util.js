'use strict';

/**
 * サーバー時刻とのオフセットミリ秒を取得する。
 *
 * @param {string} route サーバー時刻取得URL
 * @returns {number} サーバー時刻とのオフセットミリ秒
 */
export async function getServerDateOffsetAsync(route) {
    const beforeRequest = moment();
    const response = await axios.get(route);
    const afterRequest = moment();
    const serverDate = moment(response.data).add((afterRequest - beforeRequest) / 2, 'ms');
    const offset = serverDate - afterRequest;
    return offset;
}

Number.prototype.rate = function () {
    const n = this;
    return Math.round(n * 100);
}

Array.prototype.first = function () {
    const arr = this;
    return arr[0];
}

Array.prototype.last = function () {
    const arr = this;
    return arr[arr.length - 1];
}

Array.prototype.sum = function (fn) {
    const arr = this;
    return arr.reduce((carry, value, index) => {
        carry += fn(value, index);
        return carry;
    }, 0);
}

$.prototype.blink = function (duration, repeat) {
    const j = this;
    for (let i = 0; i < repeat; i++) {
        j.fadeOut(duration).fadeIn(duration);
    }
    return j;
}
