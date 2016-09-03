
## memo

* json等で表示しないものについては、表示できない。
* だけ。

* class / property meta data を拡張し、csvExpose をたべるようにする。
* class metadata はデフォでproperty名でソートする臭いので、こいつを止める。やりかたは↓
* access order は、AccessorOrderアノテーションで定義
* visitor が annotation reader を食べられれば、それでいける気がしてきた！
* groups の指定と組み合わせるだけでやっぱいける！