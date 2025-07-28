from flask import Flask, request, jsonify
app = Flask(__name__)

@app.route('/generate-timetable', methods=['POST'])
def generate():
    # 这里调用你的 Python 遗传算法生成
    result = {"status": "success", "slots": [...] }
    return jsonify(result)

if __name__ == '__main__':
    app.run(port=5000)
