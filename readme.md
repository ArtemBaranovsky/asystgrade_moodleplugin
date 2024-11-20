# ASYSTGRADE Moodle Plugin

This plugin, is designed to facilitate teachers’ activities in evaluating students’ short answers. 
The plugin uses the ASYST grading [script](https://transfer.hft-stuttgart.de/gitlab/ulrike.pado/ASYST) modified to function as a web endpoint.
The plugin requires the ASYST ML Backend to be isolated in a standalone Docker container accessible via the local network or the Internet (you could set it on plugin settings page).
ASYSTGRADE Moodle Plugin runs each time the teacher reaches some manual grading page (a part of [Essay auto-grade plugin](https://moodle.org/plugins/qtype_essayautograde) maintained by Gordon Bateson)!

The first time the plugin makes a request to the ASYST ML backend, there is a delay due to the loading of the BERT model in the Flask container.

This solution is part of the Master’s Thesis titled “Integration of a Machine Learning Backend into Assessment Processes in a PHP-based Educational Environment” at the University of Applied Sciences Stuttgart, within the Software Technology course, 2024, by Artem Baranovskyi.

## Plugin and ASYST ML Backend Interaction Concept
```mermaid
flowchart TD
    A[Moodle LMS] --> B[PHP Plugin]
    B -->|HTTP Request| C[Flask API Python Backend]
    C --> D[Pre-trained BERT Model]
    C --> E[Logistic Regression Model]
    C --> F[Sentence Transformers]
    D --> C
    E --> C
    F --> C
    C -->|Response| B
    B -->|Processed Results| A

subgraph Backend-Services
    C
    D
    E
    F
end

subgraph Docker Containers
    Backend-Services
end

B --> G{cURL}
```

## Description of Use Case processes at Sequence Diagram
```mermaid
sequenceDiagram
    participant Student
    participant Teacher
    participant Moodle
    participant PHP Plugin
    participant Flask API
    participant BERT Model

    Student->>Moodle: Submits Answer
    Teacher->>PHP Plugin: Visit Manual Grading Page
    Moodle->>PHP Plugin: Answers GET Params ($qid, $slot)
    PHP Plugin->>Flask API: Sends HTTP POST Request
    Flask API->>BERT Model: Processes Data (Embeddings)
    BERT Model-->>Flask API: Returns Processed Result
    Flask API-->>PHP Plugin: Sends Grading Response
    PHP Plugin-->>Moodle: Displays Predicted Grade
    Moodle-->>Teacher: Displays Predicted Grade
    Teacher->>Moodle: Grade (Regrade) Answer
    Moodle-->>Student: Displays Final Result
```

## Plugin components' Diagram
```mermaid
classDiagram
class local_asystgrade_lib {
+void local_asystgrade_before_footer()
+void pasteGradedMarks(array grades, array inputNames, float maxmark) // DOM Manipulation
+array prepare_api_data(quizquery database, question_attempts, referenceAnswer)
+string generate_script(array grades, array inputNames, float maxmark)
}

class client {
+send_data(array $data): bool|string
+getInstance(string $endpoint, http_client_interface $httpClient): client
-string endpoint
-http_client_interface httpClient
-static ?client instance
}

class http_client {
+post(string $url, array $data): bool|string
}

class http_client_interface {
+post(string $url, array $data): bool|string
}

class provider {
+string get_reason()
}

local_asystgrade_lib --> client : sends data to
local_asystgrade_lib <-- client : get response from
client --> http_client_interface : implements
client --> http_client : uses
http_client_interface <|-- http_client : implements
provider --> core_privacy_local_metadata_null_provider : implements

quiz_api_test --> client : sends data to
quiz_api_test <-- client : get response from
class quiz_api_test {
+setUp()
+test_quiz_api()
+create_question_category()
+create_quiz_attempt()
}
```

## ML Backend Components' Diagram
```mermaid
classDiagram
class FlaskApp {
+Flask app
+get_data()
}
class run_LR_SBERT {
+process_data(data)
+similarity(sentence_embeddings1, sentence_embeddings2)
}
class SentenceTransformer {
+encode(sentences, convert_to_tensor, show_progress_bar)
}
class LogisticRegression {
+predict(X_test)
}

class models {
+Transformer(model_name)
+Pooling(word_embedding_dimension, pooling_mode_mean_tokens, pooling_mode_cls_token, pooling_mode_max_tokens)
}

FlaskApp --> run_LR_SBERT : Calls process_data()
FlaskApp <-- run_LR_SBERT : Receives process_data()
run_LR_SBERT --> SentenceTransformer : Uses for sentence encoding
run_LR_SBERT --> LogisticRegression : Uses for prediction
run_LR_SBERT --> models : Uses Transformer and Pooling modules

class DiagramInteractions {
FlaskApp receives POST /api/autograde
FlaskApp extracts JSON data from request
FlaskApp calls process_data() in run_LR_SBERT
run_LR_SBERT encodes reference and student answers using SentenceTransformer
run_LR_SBERT calculates similarity between embeddings
run_LR_SBERT uses LogisticRegression model to predict correctness
FlaskApp returns predictions as JSON response
}
```

## How to roll out the solution
### Install locally ASYST Backend on Flask
It is not necessary to build full solution if you want just use the plugin at your existing Moodle LMS. 

To build and start the Flask ML ASYST microservice, run:

~~~bash
./local/asystgrade/deploy_backend.sh
~~~

To run prebuilt Flask ML ASYST microservice
~~~bash
./local/asystgrade/start_backend.sh 
~~~
or go to the Moodle folder with cd then enter to ./local/asystgrade/ and run
~~~bash
docker-compose up flask -d
~~~
Asyst ML Backend could be hosted and used not only at local server, but at some remoted services.
In this case it is possible to change an API address from http://127.0.0.1:5001/api/autograde to another.
![API server failure](https://transfer.hft-stuttgart.de/gitlab/ulrike.pado/asyst-moodle-plugin/-/raw/asyst-moodle-plugin/images/API%20server%20%20failure.png)

If ASYST ML microservice is running, the grade will appear at every student's answer.
![Grading result](https://transfer.hft-stuttgart.de/gitlab/ulrike.pado/asyst-moodle-plugin/-/raw/asyst-moodle-plugin/images/Grading%20result.png)


The structure of request to ASYST ML Backend: 
~~~JSON
{
  "referenceAnswer": "The reference answer",
  "studentAnswers": [
    "First Student's Answer",
    "Second Student's Answer"
  ]
}
~~~
**Explanation:**

**referenceAnswer**: This is the model answer provided by the teacher. It includes detailed explanations and grading criteria.

**studentAnswers**: This array contains the answers submitted by students. Each answer is evaluated against the reference answer.

The structure of response from ASYST ML Backend:
~~~JSON
[
  {
    "predicted_grade": "incorrect"
  },
  {
    "predicted_grade": "correct"
  }
]
~~~
**Explanation:**

**predicted_grade**: The response includes a predicted grade for each student answer, indicating whether it is “correct” or “incorrect”.

Similarity of any text now could be checked with a curl request:
```curl
curl -X POST http://127.0.0.1:5001/api/autograde -H "Content-Type: application/json" -d '{
    "referenceAnswer": "Multithreading improves the performance of a program because the processor can switch between different tasks, utilizing waiting times in one thread to process other threads. This allows for more efficient use of CPU resources.",
    "studentAnswers": [
        "Multithreading enhances a program’s performance by allowing the CPU to handle multiple tasks simultaneously. This means that while one thread is waiting for data, the CPU can process other threads, leading to more efficient use of processing power.",
        "Multithreading slows down the performance of a program because the processor gets overloaded with too many tasks at once."
    ]
}'

```
On troubleshooting during API fetch could occur CORS access errors with 500 server response.
To fix it usually necessary to set CORS at used virtual hosts like this:
~~~apacheconf
# /etc/apache2/apache2.conf
<Directory /var/www/html/moodle> 
    Options Indexes FollowSymLinks 
    AllowOverride All 
    Require all granted
    
    <IfModule mod_headers.c> 
        Header set Access-Control-Allow-Origin "*" 
        Header set Access-Control-Allow-Methods "GET, POST, OPTIONS, DELETE, PUT" 
        Header set Access-Control-Allow-Headers "Content-Type, Authorization" 
    </IfModule>
</Directory>
~~~

## Running Integration Test
To run only the plugin’s test, execute in the project’s CLI (inside the Moodle folder):
~~~bash
vendor/bin/phpunit --testsuite local_asystgrade_testsuite
~~~

To run tests please set PHPUnit. For that set params at your moodle config.php:
~~~php
$CFG->phpunit_dataroot = '/path/to/your/phpunit_dataroot'; 
$CFG->phpunit_prefix = 'phpu_';
~~~
Then run:
~~~bash
php admin/tool/phpunit/cli/init.php
~~~