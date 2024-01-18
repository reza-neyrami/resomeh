package main


import (

)

var logger *zap.Logger

const (
	MaxQueue  = 1000
	MaxWorker = 3
)

type Payload struct{}

func (p *Payload) UploadToServer() error {
	logger.Info("Uploading to server...")
	return nil
}

type Job struct {
	Payload Payload
}

var JobQueue chan Job

type Worker struct {
	WorkerPool chan chan Job
	JobChannel chan Job
	quit       chan bool
}

func NewWorker(workerPool chan chan Job) Worker {
	return Worker{
		WorkerPool: workerPool,
		JobChannel: make(chan Job),
		quit:       make(chan bool),
	}
}

func (w Worker) Start() {
	go func() {
		for {
			w.WorkerPool <- w.JobChannel
			select {
			case job := <-w.JobChannel:
				err := job.Payload.UploadToServer()
				if err != nil {
					fmt.Println(err)
					return
				}
			case <-w.quit:
				return
			}
		}
	}()
}

func (w Worker) Stop() {
	go func() {
		w.quit <- true
	}()
}

type Dispatcher struct {
	WorkerPool chan chan Job
	maxWorkers int
}

func NewDispatcher(maxWorkers int) *Dispatcher {
	pool := make(chan chan Job, maxWorkers)
	return &Dispatcher{WorkerPool: pool, maxWorkers: maxWorkers}
}

func (d *Dispatcher) Run() {
	for i := 0; i < d.maxWorkers; i++ {
		worker := NewWorker(d.WorkerPool)
		worker.Start()
	}

	go d.dispatch()
}

func (d *Dispatcher) dispatch() {
	for {
		select {
		case job := <-JobQueue:
			go func(job Job) {
				jobChannel := <-d.WorkerPool
				jobChannel <- job
			}(job)
		}
	}
}

func payloadHandler(c *gin.Context) {
	work := Job{Payload: Payload{}}
	JobQueue <- work
	c.String(http.StatusOK, "Payload received")
}

func main() {
	configureLogger()
	defer func(logger *zap.Logger) {
		err := logger.Sync()
		if err != nil {
			fmt.Println(err)
		}
	}(logger)

	JobQueue = make(chan Job, MaxQueue)
	dispatcher := NewDispatcher(MaxWorker)
	dispatcher.Run()

	router := gin.New() // از gin.New() بجای gin.Default() استفاده کنید
	router.GET("/payload", payloadHandler)

	err := http.ListenAndServe(":8006", router)
	if err != nil {
		logger.Error("Failed to start the server", zap.Error(err))
	}
}

func configureLogger() {
	config := zap.NewDevelopmentConfig()
	config.EncoderConfig.EncodeLevel = zapcore.CapitalColorLevelEncoder
	logger, _ = config.Build()
	zap.ReplaceGlobals(logger)
}
