<?php

namespace Orhanerday\OpenAi;

use Exception;

class OpenAi
{
    private string $engine = "davinci";
    private string $model = "text-davinci-002";
    private string $chatModel = "gpt-3.5-turbo";
    private string $assistantsBetaVersion = "v1";
    private array $headers;
    private array $contentTypes;
    private int $timeout = 0;
    private object $stream_method;
    private string $customUrl = "";
    private string $proxy = "";
    private array $curlInfo = [];

    public function __construct($OPENAI_API_KEY)
    {
        $this->contentTypes = [
            "application/json" => "Content-Type: application/json",
            "multipart/form-data" => "Content-Type: multipart/form-data",
        ];

        $this->headers = [
            $this->contentTypes["application/json"],
            "Authorization: Bearer $OPENAI_API_KEY",
        ];
    }

    /**
     * @return array
     * Remove this method from your code before deploying
     */
    public function getCURLInfo()
    {
        return $this->curlInfo;
    }

    /**
     * @return bool|string
     */
    public function listModels()
    {
        $url = Url::fineTuneModel();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $model
     * @return bool|string
     */
    public function retrieveModel($model)
    {
        $model = "/$model";
        $url = Url::fineTuneModel().$model;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $opts
     * @return bool|string
     * @deprecated
     */
    public function complete($opts)
    {
        $engine = $opts['engine'] ?? $this->engine;
        $url = Url::completionURL($engine);
        unset($opts['engine']);
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param        $opts
     * @param  null  $stream
     * @return bool|string
     * @throws Exception
     */
    public function completion($opts, $stream = null)
    {
        if (array_key_exists('stream', $opts) && $opts['stream']) {
            if ($stream == null) {
                throw new Exception(
                    'Please provide a stream function. Check https://github.com/orhanerday/open-ai#stream-example for an example.'
                );
            }

            $this->stream_method = $stream;
        }

        $opts['model'] = $opts['model'] ?? $this->model;
        $url = Url::completionsURL();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function createEdit($opts)
    {
        $url = Url::editsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function image($opts)
    {
        $url = Url::imageUrl()."/generations";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function imageEdit($opts)
    {
        $url = Url::imageUrl()."/edits";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function createImageVariation($opts)
    {
        $url = Url::imageUrl()."/variations";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param $opts
     * @return bool|string
     * @deprecated
     */
    public function search($opts)
    {
        $engine = $opts['engine'] ?? $this->engine;
        $url = Url::searchURL($engine);
        unset($opts['engine']);
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param $opts
     * @return bool|string
     * @deprecated
     */
    public function answer($opts)
    {
        $url = Url::answersUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param $opts
     * @return bool|string
     * @deprecated
     */
    public function classification($opts)
    {
        $url = Url::classificationsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function moderation($opts)
    {
        $url = Url::moderationUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param        $opts
     * @param  null  $stream
     * @return bool|string
     * @throws Exception
     */
    public function chat($opts, $stream = null)
    {
        if (array_key_exists('stream', $opts) && $opts['stream']) {
            if ($stream == null) {
                throw new Exception(
                    'Please provide a stream function. Check https://github.com/orhanerday/open-ai#stream-example for an example.'
                );
            }

            $this->stream_method = $stream;
        }

        $opts['model'] = $opts['model'] ?? $this->chatModel;
        $url = Url::chatUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param        $opts
     * @param  null  $stream
     * @return bool|string
     * @throws Exception
     */
    public function response($opts, $stream = null)
    {
        if (array_key_exists('stream', $opts) && $opts['stream']) {
            if ($stream == null) {
                throw new Exception(
                    'Please provide a stream function. Check https://github.com/orhanerday/open-ai#stream-example for an example.'
                );
            }

            $this->stream_method = $stream;
        }

        $url = Url::responsesUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param $response_id
     * @return bool|string
     */
    public function retrieveResponse($response_id)
    {
        $response_id = "/$response_id";
        $url = Url::responsesUrl().$response_id;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $response_id
     * @return bool|string
     */
    public function deleteResponse($response_id)
    {
        $response_id = "/$response_id";
        $url = Url::responsesUrl().$response_id;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    /**
     * @param $response_id
     * @return bool|string
     */
    public function cancelResponse($response_id)
    {
        $response_id = "/$response_id/cancel";
        $url = Url::responsesUrl().$response_id;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST');
    }

    /**
     * @param        $response_id
     * @param  array $query
     * @return bool|string
     */
    public function listResponseInputItems($response_id, $query = [])
    {
        $url = Url::responsesUrl()."/$response_id/input_items";
        if (! empty($query)) {
            $url .= '?'.http_build_query($query);
        }
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param  array $data
     * @return bool|string
     */
    public function createConversation($data = [])
    {
        $url = Url::conversationsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param $conversation_id
     * @return bool|string
     */
    public function retrieveConversation($conversation_id)
    {
        $url = Url::conversationsUrl()."/$conversation_id";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param        $conversation_id
     * @param  array $data
     * @return bool|string
     */
    public function modifyConversation($conversation_id, $data)
    {
        $url = Url::conversationsUrl()."/$conversation_id";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param $conversation_id
     * @return bool|string
     */
    public function deleteConversation($conversation_id)
    {
        $url = Url::conversationsUrl()."/$conversation_id";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    /**
     * @param        $conversation_id
     * @param  array $data
     * @return bool|string
     */
    public function createConversationItems($conversation_id, $data)
    {
        $url = Url::conversationsUrl()."/$conversation_id/items";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param        $conversation_id
     * @param  array $query
     * @return bool|string
     */
    public function listConversationItems($conversation_id, $query = [])
    {
        $url = Url::conversationsUrl()."/$conversation_id/items";
        if (! empty($query)) {
            $url .= '?'.http_build_query($query);
        }
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $conversation_id
     * @param $item_id
     * @return bool|string
     */
    public function retrieveConversationItem($conversation_id, $item_id)
    {
        $url = Url::conversationsUrl()."/$conversation_id/items/$item_id";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $conversation_id
     * @param $item_id
     * @return bool|string
     */
    public function deleteConversationItem($conversation_id, $item_id)
    {
        $url = Url::conversationsUrl()."/$conversation_id/items/$item_id";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    /**
     * @param  array $data
     * @return bool|string
     */
    public function createVectorStore($data = [])
    {
        $url = Url::vectorStoresUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param  array $query
     * @return bool|string
     */
    public function listVectorStores($query = [])
    {
        $url = Url::vectorStoresUrl();
        if (! empty($query)) {
            $url .= '?'.http_build_query($query);
        }
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $vector_store_id
     * @return bool|string
     */
    public function retrieveVectorStore($vector_store_id)
    {
        $url = Url::vectorStoresUrl()."/$vector_store_id";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param        $vector_store_id
     * @param  array $data
     * @return bool|string
     */
    public function modifyVectorStore($vector_store_id, $data)
    {
        $url = Url::vectorStoresUrl()."/$vector_store_id";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param $vector_store_id
     * @return bool|string
     */
    public function deleteVectorStore($vector_store_id)
    {
        $url = Url::vectorStoresUrl()."/$vector_store_id";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    /**
     * @param        $vector_store_id
     * @param  array $data
     * @return bool|string
     */
    public function searchVectorStore($vector_store_id, $data)
    {
        $url = Url::vectorStoresUrl()."/$vector_store_id/search";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param        $vector_store_id
     * @param  array $data
     * @return bool|string
     */
    public function createVectorStoreFile($vector_store_id, $data)
    {
        $url = Url::vectorStoresUrl()."/$vector_store_id/files";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param        $vector_store_id
     * @param  array $query
     * @return bool|string
     */
    public function listVectorStoreFiles($vector_store_id, $query = [])
    {
        $url = Url::vectorStoresUrl()."/$vector_store_id/files";
        if (! empty($query)) {
            $url .= '?'.http_build_query($query);
        }
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $vector_store_id
     * @param $file_id
     * @return bool|string
     */
    public function retrieveVectorStoreFile($vector_store_id, $file_id)
    {
        $url = Url::vectorStoresUrl()."/$vector_store_id/files/$file_id";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param        $vector_store_id
     * @param        $file_id
     * @param  array $data
     * @return bool|string
     */
    public function updateVectorStoreFileAttributes($vector_store_id, $file_id, $data)
    {
        $url = Url::vectorStoresUrl()."/$vector_store_id/files/$file_id";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param $vector_store_id
     * @param $file_id
     * @return bool|string
     */
    public function deleteVectorStoreFile($vector_store_id, $file_id)
    {
        $url = Url::vectorStoresUrl()."/$vector_store_id/files/$file_id";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    /**
     * @param $vector_store_id
     * @param $file_id
     * @return bool|string
     */
    public function retrieveVectorStoreFileContent($vector_store_id, $file_id)
    {
        $url = Url::vectorStoresUrl()."/$vector_store_id/files/$file_id/content";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param        $vector_store_id
     * @param  array $data
     * @return bool|string
     */
    public function createVectorStoreFileBatch($vector_store_id, $data)
    {
        $url = Url::vectorStoresUrl()."/$vector_store_id/file_batches";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param $vector_store_id
     * @param $batch_id
     * @return bool|string
     */
    public function retrieveVectorStoreFileBatch($vector_store_id, $batch_id)
    {
        $url = Url::vectorStoresUrl()."/$vector_store_id/file_batches/$batch_id";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $vector_store_id
     * @param $batch_id
     * @return bool|string
     */
    public function cancelVectorStoreFileBatch($vector_store_id, $batch_id)
    {
        $url = Url::vectorStoresUrl()."/$vector_store_id/file_batches/$batch_id/cancel";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST');
    }

    /**
     * @param        $vector_store_id
     * @param        $batch_id
     * @param  array $query
     * @return bool|string
     */
    public function listVectorStoreFileBatchFiles($vector_store_id, $batch_id, $query = [])
    {
        $url = Url::vectorStoresUrl()."/$vector_store_id/file_batches/$batch_id/files";
        if (! empty($query)) {
            $url .= '?'.http_build_query($query);
        }
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $prompt_id
     * @return bool|string
     */
    public function retrievePrompt($prompt_id)
    {
        $url = Url::promptsUrl()."/$prompt_id";
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function transcribe($opts)
    {
        $url = Url::transcriptionsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function translate($opts)
    {
        $url = Url::translationsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function uploadFile($opts)
    {
        $url = Url::filesUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @return bool|string
     */
    public function listFiles()
    {
        $url = Url::filesUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $file_id
     * @return bool|string
     */
    public function retrieveFile($file_id)
    {
        $file_id = "/$file_id";
        $url = Url::filesUrl().$file_id;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $file_id
     * @return bool|string
     */
    public function retrieveFileContent($file_id)
    {
        $file_id = "/$file_id/content";
        $url = Url::filesUrl().$file_id;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $file_id
     * @return bool|string
     */
    public function deleteFile($file_id)
    {
        $file_id = "/$file_id";
        $url = Url::filesUrl().$file_id;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function createFineTune($opts)
    {
        $url = Url::fineTuneUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @return bool|string
     */
    public function listFineTunes()
    {
        $url = Url::fineTuneUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $fine_tune_id
     * @return bool|string
     */
    public function retrieveFineTune($fine_tune_id)
    {
        $fine_tune_id = "/$fine_tune_id";
        $url = Url::fineTuneUrl().$fine_tune_id;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $fine_tune_id
     * @return bool|string
     */
    public function cancelFineTune($fine_tune_id)
    {
        $fine_tune_id = "/$fine_tune_id/cancel";
        $url = Url::fineTuneUrl().$fine_tune_id;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST');
    }

    /**
     * @param $fine_tune_id
     * @return bool|string
     */
    public function listFineTuneEvents($fine_tune_id)
    {
        $fine_tune_id = "/$fine_tune_id/events";
        $url = Url::fineTuneUrl().$fine_tune_id;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $fine_tune_id
     * @return bool|string
     */
    public function deleteFineTune($fine_tune_id)
    {
        $fine_tune_id = "/$fine_tune_id";
        $url = Url::fineTuneModel().$fine_tune_id;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    /**
     * @param
     * @return bool|string
     * @deprecated
     */
    public function engines()
    {
        $url = Url::enginesUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $engine
     * @return bool|string
     * @deprecated
     */
    public function engine($engine)
    {
        $url = Url::engineUrl($engine);
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function embeddings($opts)
    {
        $url = Url::embeddings();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param array $data
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. Migrate to the Responses API (response()) and Conversations API.
     */
    public function createAssistant($data)
    {
        $data['model'] = $data['model'] ?? $this->chatModel;
        $this->addAssistantsBetaHeader();
        $url = Url::assistantsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param string $assistantId
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. Migrate to the Responses API (response()) and Conversations API.
     */
    public function retrieveAssistant($assistantId)
    {
        $this->addAssistantsBetaHeader();
        $url = Url::assistantsUrl() . '/' . $assistantId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $assistantId
     * @param array $data
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. Migrate to the Responses API (response()) and Conversations API.
     */
    public function modifyAssistant($assistantId, $data)
    {
        $this->addAssistantsBetaHeader();
        $url = Url::assistantsUrl() . '/' . $assistantId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param string $assistantId
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. Migrate to the Responses API (response()) and Conversations API.
     */
    public function deleteAssistant($assistantId)
    {
        $this->addAssistantsBetaHeader();
        $url = Url::assistantsUrl() . '/' . $assistantId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    /**
     * @param array $query
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. Migrate to the Responses API (response()) and Conversations API.
     */
    public function listAssistants($query = [])
    {
        $this->addAssistantsBetaHeader();
        $url = Url::assistantsUrl();
        if (count($query) > 0) {
            $url .= '?' . http_build_query($query);
        }
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $assistantId
     * @param string $fileId
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. Migrate to the Responses API (response()) and Conversations API.
     */
    public function createAssistantFile($assistantId, $fileId)
    {
        $this->addAssistantsBetaHeader();
        $url = Url::assistantsUrl() . '/' . $assistantId . '/files';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', ['file_id' => $fileId]);
    }

    /**
     * @param string $assistantId
     * @param string $fileId
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. Migrate to the Responses API (response()) and Conversations API.
     */
    public function retrieveAssistantFile($assistantId, $fileId)
    {
        $this->addAssistantsBetaHeader();
        $url = Url::assistantsUrl() . '/' . $assistantId . '/files/' . $fileId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $assistantId
     * @param array $query
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. Migrate to the Responses API (response()) and Conversations API.
     */
    public function listAssistantFiles($assistantId, $query = [])
    {
        $this->addAssistantsBetaHeader();
        $url = Url::assistantsUrl() . '/' . $assistantId . '/files';
        if (count($query) > 0) {
            $url .= '?' . http_build_query($query);
        }
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $assistantId
     * @param string $fileId
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. Migrate to the Responses API (response()) and Conversations API.
     */
    public function deleteAssistantFile($assistantId, $fileId)
    {
        $this->addAssistantsBetaHeader();
        $url = Url::assistantsUrl() . '/' . $assistantId . '/files/' . $fileId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    /**
     * @param array $data
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. Migrate to the Conversations API (createConversation()).
     */
    public function createThread($data = [])
    {
        $this->addAssistantsBetaHeader();
        $url = Url::threadsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param string $threadId
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. Migrate to the Conversations API (retrieveConversation()).
     */
    public function retrieveThread($threadId)
    {
        $this->addAssistantsBetaHeader();
        $url = Url::threadsUrl() . '/' . $threadId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $threadId
     * @param array $data
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. Migrate to the Conversations API (modifyConversation()).
     */
    public function modifyThread($threadId, $data)
    {
        $this->addAssistantsBetaHeader();
        $url = Url::threadsUrl() . '/' . $threadId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param string $threadId
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. Migrate to the Conversations API (deleteConversation()).
     */
    public function deleteThread($threadId)
    {
        $this->addAssistantsBetaHeader();
        $url = Url::threadsUrl() . '/' . $threadId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'DELETE');
    }

    /**
     * @param string $threadId
     * @param array $data
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. Migrate to the Conversations API (createConversationItems()).
     */
    public function createThreadMessage($threadId, $data)
    {
        $this->addAssistantsBetaHeader();
        $url = Url::threadsUrl() . '/' . $threadId . '/messages';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param string $threadId
     * @param string $messageId
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. Migrate to the Conversations API (retrieveConversationItem()).
     */
    public function retrieveThreadMessage($threadId, $messageId)
    {
        $this->addAssistantsBetaHeader();
        $url = Url::threadsUrl() . '/' . $threadId . '/messages/' . $messageId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $threadId
     * @param string $messageId
     * @param array $data
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. Migrate to the Conversations API.
     */
    public function modifyThreadMessage($threadId, $messageId, $data)
    {
        $this->addAssistantsBetaHeader();
        $url = Url::threadsUrl() . '/' . $threadId . '/messages/' . $messageId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param string $threadId
     * @param array $query
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. Migrate to the Conversations API (listConversationItems()).
     */
    public function listThreadMessages($threadId, $query = [])
    {
        $this->addAssistantsBetaHeader();
        $url = Url::threadsUrl() . '/' . $threadId . '/messages';
        if (count($query) > 0) {
            $url .= '?' . http_build_query($query);
        }
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $threadId
     * @param string $messageId
     * @param string $fileId
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. Migrate to the Responses/Conversations API.
     */
    public function retrieveMessageFile($threadId, $messageId, $fileId)
    {
        $this->addAssistantsBetaHeader();
        $url = Url::threadsUrl() . '/' . $threadId . '/messages/' . $messageId . '/files/' . $fileId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $threadId
     * @param string $messageId
     * @param array $query
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. Migrate to the Responses/Conversations API.
     */
    public function listMessageFiles($threadId, $messageId, $query = [])
    {
        $this->addAssistantsBetaHeader();
        $url = Url::threadsUrl() . '/' . $threadId . '/messages/' . $messageId . '/files';
        if (count($query) > 0) {
            $url .= '?' . http_build_query($query);
        }
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $threadId
     * @param array $data
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. Migrate to the Responses API (response() with conversation reference).
     */
    public function createRun($threadId, $data, $stream = null)
    {
        if (array_key_exists('stream', $data) && $data['stream']) {
            if ($stream == null) {
                throw new Exception(
                    'Please provide a stream function. Check https://github.com/orhanerday/open-ai#stream-example for an example.'
                );
            }

            $this->stream_method = $stream;
        }
        
        $this->addAssistantsBetaHeader();
        $url = Url::threadsUrl() . '/' . $threadId . '/runs';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param string $threadId
     * @param string $runId
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. Migrate to the Responses API (retrieveResponse()).
     */
    public function retrieveRun($threadId, $runId)
    {
        $this->addAssistantsBetaHeader();
        $url = Url::threadsUrl() . '/' . $threadId . '/runs/' . $runId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $threadId
     * @param string $runId
     * @param array $data
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. Migrate to the Responses API.
     */
    public function modifyRun($threadId, $runId, $data)
    {
        $this->addAssistantsBetaHeader();
        $url = Url::threadsUrl() . '/' . $threadId . '/runs/' . $runId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param string $threadId
     * @param array $query
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. Migrate to the Responses API.
     */
    public function listRuns($threadId, $query = [])
    {
        $this->addAssistantsBetaHeader();
        $url = Url::threadsUrl() . '/' . $threadId . '/runs';
        if (count($query) > 0) {
            $url .= '?' . http_build_query($query);
        }
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $threadId
     * @param string $runId
     * @param array $outputs
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. In Responses API, tool call loops are managed by the application code.
     */
    public function submitToolOutputs($threadId, $runId, $outputs, $stream = null)
    {
        if (array_key_exists('stream', $outputs) && $outputs['stream']) {
            if ($stream == null) {
                throw new Exception(
                    'Please provide a stream function. Check https://github.com/orhanerday/open-ai#stream-example for an example.'
                );
            }

            $this->stream_method = $stream;
        }
        
        $this->addAssistantsBetaHeader();
        $url = Url::threadsUrl() . '/' . $threadId . '/runs/' . $runId . '/submit_tool_outputs';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $outputs);
    }

    /**
     * @param string $threadId
     * @param string $runId
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. Migrate to the Responses API (cancelResponse()).
     */
    public function cancelRun($threadId, $runId)
    {
        $this->addAssistantsBetaHeader();
        $url = Url::threadsUrl() . '/' . $threadId . '/runs/' . $runId . '/cancel';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST');
    }

    /**
     * @param array $data
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. Migrate to the Responses API (response()).
     */
    public function createThreadAndRun($data)
    {
        $this->addAssistantsBetaHeader();
        $url = Url::threadsUrl() . '/runs';
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $data);
    }

    /**
     * @param string $threadId
     * @param string $runId
     * @param string $stepId
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. In Responses API, run steps are represented as items in response.output.
     */
    public function retrieveRunStep($threadId, $runId, $stepId)
    {
        $this->addAssistantsBetaHeader();
        $url = Url::threadsUrl() . '/' . $threadId . '/runs/' . $runId . '/steps/' . $stepId;
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param string $threadId
     * @param string $runId
     * @param array $query
     * @return bool|string
     * @deprecated The Assistants API is being shut down on August 26, 2026. In Responses API, run steps are represented as items in response.output.
     */
    public function listRunSteps($threadId, $runId, $query = [])
    {
        $this->addAssistantsBetaHeader();
        $url = Url::threadsUrl() . '/' . $threadId . '/runs/' . $runId . '/steps';
        if (count($query) > 0) {
            $url .= '?' . http_build_query($query);
        }
        $this->baseUrl($url);

        return $this->sendRequest($url, 'GET');
    }

    /**
     * @param $opts
     * @return bool|string
     */
    public function tts($opts)
    {
        $url = Url::ttsUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @param  int  $timeout
     */
    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @param  string  $proxy
     */
    public function setProxy(string $proxy)
    {
        if ($proxy && strpos($proxy, '://') === false) {
            $proxy = 'https://'.$proxy;
        }
        $this->proxy = $proxy;
    }

    /**
     * @param  string  $customUrl
     * @deprecated
     */

    /**
     * @param  string  $customUrl
     * @return void
     */
    public function setCustomURL(string $customUrl)
    {
        if ($customUrl != "") {
            $this->customUrl = $customUrl;
        }
    }

    /**
     * @param  string  $customUrl
     * @return void
     */
    public function setBaseURL(string $customUrl)
    {
        if ($customUrl != '') {
            $this->customUrl = $customUrl;
        }
    }

    /**
     * @param  array  $header
     * @return void
     */
    public function setHeader(array $header)
    {
        if ($header) {
            foreach ($header as $key => $value) {
                $this->headers[$key] = $value;
            }
        }
    }

    /**
     * @param  string  $org
     */
    public function setORG(string $org)
    {
        if ($org != "") {
            $this->headers[] = "OpenAI-Organization: $org";
        }
    }
    
    /**
     * @param  string  $org
     */
    public function setAssistantsBetaVersion(string $version)
    {
        if ($version != "") {
            $this->assistantsBetaVersion = $version;
        }
    }

    /**
     * @return void
     */
    private function addAssistantsBetaHeader(){ 
        $this->headers[] = 'OpenAI-Beta: assistants='.$this->assistantsBetaVersion;
    }
    

    /**
     * @param  string  $url
     * @param  string  $method
     * @param  array   $opts
     * @return bool|string
     */
    private function sendRequest(string $url, string $method, array $opts = [])
    {
        $post_fields = json_encode($opts);

        if (array_key_exists('file', $opts) || array_key_exists('image', $opts)) {
            $this->headers[0] = $this->contentTypes["multipart/form-data"];
            $post_fields = $opts;
        } else {
            $this->headers[0] = $this->contentTypes["application/json"];
        }
        $curl_info = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $post_fields,
            CURLOPT_HTTPHEADER => $this->headers,
        ];

        if ($opts == []) {
            unset($curl_info[CURLOPT_POSTFIELDS]);
        }

        if (! empty($this->proxy)) {
            $curl_info[CURLOPT_PROXY] = $this->proxy;
        }

        if (array_key_exists('stream', $opts) && $opts['stream']) {
            $curl_info[CURLOPT_WRITEFUNCTION] = $this->stream_method;
        }

        $curl = curl_init();

        curl_setopt_array($curl, $curl_info);
        $response = curl_exec($curl);

        $info = curl_getinfo($curl);
        $this->curlInfo = $info;

        curl_close($curl);

        if (! $response) {
            throw new Exception(curl_error($curl));
        }

        return $response;
    }

    /**
     * @param  string  $url
     */
    private function baseUrl(string &$url)
    {
        if ($this->customUrl != "") {
            $url = str_replace(Url::ORIGIN, $this->customUrl, $url);
        }
    }
}
