db.createUser({
    user: "user",
    pwd: "123456",
    roles: [
        {
            role: "readWrite",
            db: "yii2-base-models"
        }
    ]
});