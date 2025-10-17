// apphost.cs  -- single-file AppHost (experimental in Aspire 9.5)
#:sdk Microsoft.NET.Sdk
#:sdk Aspire.AppHost.Sdk@9.5.1
#:package Aspire.Hosting.AppHost@9.5.1
#:package Aspire.Hosting.MySql@9.5.0
#:package LocalStack.Aspire.Hosting@9.4.0-rc.1
#:property PublishAot=false

#nullable enable
using Aspire.Hosting;
using Aspire.Hosting.ApplicationModel;
using Aspire.Hosting.Dcp;
using Aspire.Hosting.LocalStack.Container;
using Microsoft.Extensions.Hosting;

Environment.SetEnvironmentVariable("ASPIRE_ALLOW_UNSECURED_TRANSPORT", "true"); // local dev
// ^ for local http; prefer https certs for real work

var builder = DistributedApplication.CreateBuilder(args);

// --- MySQL (container + DB) ----------------------
var mysql = builder.AddMySql("mysql")
                    .WithDataVolume()
                    .WithPhpMyAdmin()
                   .WithLifetime(ContainerLifetime.Persistent); // speed up restarts

var mysqldb = mysql.AddDatabase("mysqldb");

// var localstack = builder.AddLocalStack("localstack", configureContainer: container =>
//         {
//             container.Lifetime = ContainerLifetime.Session;
//             container.DebugLevel = 1;
//             container.LogLevel = LocalStackLogLevel.Debug;
//         });


// --- LocalStack (AWS emulation: S3 + Secrets Manager) ----
var localstack = builder.AddContainer("localstack", "localstack/localstack:latest")
    .WithEnvironment("DEFAULT_REGION", "us-east-1")
    .WithEnvironment("LS_LOG", "error")
    // optional: speed start by preselecting services
    //.WithEnvironment("SERVICES", "s3,secretsmanager")
    .WithHttpEndpoint(port: 4566, targetPort: 4566, name: "http") // HTTP endpoint for health check
    .WithHttpHealthCheck("/_localstack/health"); // simple health

// --- PHP app (Dockerfile build) ------------------
var php = builder.AddDockerfile("phpapp", "./phpapp")
    .WithHttpEndpoint(targetPort: 80, name: "http")
    // DB config: pass as envs or use a small php config file
    .WithEnvironment("DB_HOST", "mysql")
    .WithEnvironment("DB_PORT", "3306")
    .WithEnvironment("DB_NAME", "mysqldb")
    .WithEnvironment("DB_USER", "root") // or a dedicated user you create at startup
    .WithEnvironment("DB_PASSWORD", mysql.Resource.PasswordParameter) // generated root pw
    .WithEnvironment("AWS_ENDPOINT", "http://localstack:4566")
    .WithReference(mysqldb)
    .WaitFor(mysqldb)        // wait until DB is ready
    .WaitFor(localstack)    // wait until LocalStack edge is healthy
    ;

// builder.UseLocalStack(localstack);


builder.Build().Run();