M.local_asystgrade = {
    init: function(Y, jsData) {
        const isDebuggingEnabled = true; // Set this to false in production

        function log(message) {
            if (isDebuggingEnabled) {
                console.log(message);
            }
        }

        window.gradeData = jsData;
        document.addEventListener('DOMContentLoaded', function() {
            const apiEndpoint = M.cfg.wwwroot + '/local/asystgrade/api.php';
            const maxmark = document.querySelectorAll("input[name$='-maxmark']")[0].value;
            const answerDivs = document.querySelectorAll(".qtype_essay_response");
            const studentAnswers = Array.from(answerDivs).map(element => element.innerText || element.value);

            const gradesDataRequest = {
                referenceAnswer: document.querySelectorAll(".essay .qtext p")[0].innerHTML,
                studentAnswers: studentAnswers
            };

            fetch(apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(gradesDataRequest)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.grades) {
                        log(data.grades);
                        updateMarks(data.grades);
                    } else {
                        error('Error in grade response:', data.error);
                    }
                    // Return the data to keep the Promise chain intact
                    return data;
                })
                .catch(error => {
                    error('Error:', error);
                    // Return the error to keep the Promise chain intact
                    throw error;
                });

            function updateMarks(grades) {
                const inputs = document.querySelectorAll("input[name$='_-mark']");

                grades.forEach((grade, index) => {
                    const predictedGrade = grade.predicted_grade === 'correct' ? maxmark : 0;

                    if (inputs[index]) {
                        inputs[index].value = predictedGrade;
                    } else {
                        error(`No grade input found for index: ${index}`);
                    }
                });
            }
        });
    }
};
