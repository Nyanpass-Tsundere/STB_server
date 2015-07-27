# STB機上盒伺服器端程式

## 功能

把節目的爛／讚透過phpDataObject存進資料庫。


## 需求

請設定Rewrite，把 /api/ 指向 backen.php。

## API路徑與欄位

### 傳送讚、爛、收視 https://example.com/api/sentStatus

POST/GET欄位：

    UID：使用者ID

    Channel：頻道(名稱，請從「電視內容分析系統」接過來)

    Program：節目(名稱，請從「電視內容分析系統」接過來)

    Status：-1爛，0路過，1讚(其實是個Integer，你可以定義更多用法XD)

返回訊息欄位：

    API_status：狀態，0新增成功(無資料)，1更新成功(已經有資料)

    API_msg：顯示簡單的訊息

    API_det：null


### 傳送評價 http://example.com/api/sentComment

POST/GET欄位：

    UID：使用者ID

    Channel：頻道(名稱，請從「電視內容分析系統」接過來)

    Program：節目(名稱，請從「電視內容分析系統」接過來)

    Comment：文字訊息(mysql的text)

返回訊息欄位：

    API_status：狀態，0新增成功(無資料)，1更新成功(已經有資料)

    API_msg：顯示簡單的訊息

    API_det：null

### 接收頻道的爛與讚 http://example.com/api/getProgramStatus

POST/GET欄位：

    UID：使用者ID

    Program：頻道(名稱／ID)


